<?php
namespace app\components;

use yii\log\Target;
use yii\base\InvalidConfigException;

/**
 * StdTarget logs messages to stdin or stdout
 *
 */
class StdTarget extends Target
{
    /**
     * @var string the stream to use, either 'stdout' or 'stderr'. Default is 'stdout'.
     */
    public $stream;

    /**
     * Writes a log message to stdout or stderr
     * @throws InvalidConfigException if unable to open the stream for writing
     */
    public function export()
    {
        $text = implode("\n", array_map([$this, 'formatMessage'], $this->messages)) . "\n";
        $stream = $this->stream==='stderr' ? 'stderr' : 'stdout';
        if (($fp = @fopen("php://$stream",'w')) === false) {
            throw new InvalidConfigException("Unable to append to $stream");
        }
        fwrite($fp, $text);
        fclose($fp);
    }
}
