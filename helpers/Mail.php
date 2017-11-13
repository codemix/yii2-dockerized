<?php
namespace app\helpers;

use Yii;
use Swift_SwiftException;

/**
 * A helper class for sending mails
 */
class Mail
{

    /**
     * Send an user email
     *
     * The view is rendered from `@app/mails/user` with the
     * `@app/mails/layouts/user` layout applied.
     *
     * The sender can be specified in `$params['from']` and defaults to the
     * `mail.from` application parameter.
     *
     * @param app\models\User $user the recipient user object
     * @param string $view name of the view file to render from `@app/mail/user`
     * @param array $params additional view parameters
     * @return bool whether the mail was sent successfully
     */
    public static function toUser($user, $view, $params = [])
    {
        $params['user'] = $user;
        return self::to($user->email, 'user/' . $view, 'layouts/user', $params);
    }

    /**
     * Send an email
     *
     * The sender can be specified in `$params['from']` and defaults to the
     * `mail.from` application parameter.
     *
     * @param string|string[] $recipient one or more recipient addresses.
     * Optionally as array of the form `[$email => $name, ...]`
     * @param string $view name of the view file to render from `@app/mail`
     * @param string $layout the `htmlLayout` parameter. If `false`, a pure
     * text email without layout is rendered.
     * @param array $params additional view parameters
     * @return bool whether the mail was sent successfully
     */
    public static function to($recipient, $view, $layout, $params = [])
    {
        $mailer = Yii::$app->mailer;
        $appParams = Yii::$app->params;
        if ($layout === false) {
            $mailer->textLayout = false;
        } else {
            $mailer->htmlLayout = $layout;
        }
        $catchAll = $appParams['mail.catchAll'];
        if (!empty($catchAll)) {
            Yii::info("Using catchAll email. Original recipient was ".print_r($recipient, true), __METHOD__);
            $recipient = $catchAll;
        }
        if (empty($recipient)) {
            return false;
        }
        try {
            $message = $mailer
                ->compose($layout === false ? ['text' => $view] : $view, $params)
                ->setTo($recipient);

            if (!$message->getFrom()) {
                $from = isset($params['from']) ? $params['from'] : $appParams['mail.from'];
                $message->setFrom($from);
            }

            $recipientString = print_r($recipient, true);
            if ($message->send()) {
                Yii::info("Sent $view to $recipientString", __METHOD__);
                return true;
            } else {
                Yii::warning("Failed to send $view to $recipientString", __METHOD__);
                return false;
            }
        } catch (Swift_SwiftException $e) {
            $type = get_class($e);
            $message = $e->getMessage();
            $trace = $e->getTraceAsString();
            Yii::warning("Swift exception $type:\n$message\n\n$trace", __METHOD__);
        }
        return false;
    }
}
