<?php

$I = new AcceptanceTester($scenario);
$I->wantTo('load enquiry modal, submission and enquiry recorded');
$I->ensureNoEnquiryEntry();
$I->amOnPage('/');
$I->seeElement('//button[text()="Lodge Enquiry"]');
$I->click('//button[text()="Lodge Enquiry"]');
$I->waitForElementVisible('#enquiry-dialog');
$I->see("Lodge Enquiry", '#enquiry-dialog .modal-title');
$I->fillField('#enquiry-dialog #name', 'Jim');
$I->fillField('#enquiry-dialog #email', 'jim@google.com');
$I->fillField('#enquiry-dialog #phone', '57425767653');
$I->selectOption('#enquiry-dialog #preferred_method', 'phone');
$I->fillField('#enquiry-dialog #enquiry', 'Test');
$I->click('#enquiry-dialog #submitBtn');
$I->wait(1);
$I->acceptPopup();
$I->hasEnquiryEntry(1);