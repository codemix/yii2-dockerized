<?php

use tests\codeception\_pages\LoginPage;
use tests\codeception\fixtures\UserFixture;

/* @var $scenario Codeception\Scenario */

$fixture = new UserFixture();
$fixture->load();

$user = $fixture->getModel('default');

$I = new FunctionalTester($scenario);
$I->wantTo('ensure that login works');

$loginPage = LoginPage::openBy($I);

$I->see('Login', 'h1');

$I->amGoingTo('try to login with empty credentials');
$loginPage->login('', '');
$I->expectTo('see validations errors');
$I->see('Username cannot be blank.');
$I->see('Password cannot be blank.');

$I->amGoingTo('try to login with a not existing username');
$loginPage->login('does-not-exist', 'wrong');
$I->expectTo('see validations errors');
$I->see('Incorrect username or password.');

$I->amGoingTo('try to login with a wrong password');
$loginPage->login($user->username, 'wrong');
$I->expectTo('see validations errors');
$I->see('Incorrect username or password.');

$I->amGoingTo('try to login with correct credentials');
$loginPage->login($user->username, 'password_0');
$I->expectTo('see user info');
$I->see("Logout ({$user->username})");
