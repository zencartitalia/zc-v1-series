<?php
/**
 * File contains zc_install tests and some general preliminary test-environment setup scripts
 *
 * @package tests
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: testInstall.php 19690 2011-10-04 16:41:45Z drbyte $
 */
require_once 'zcCommonTestResources.php';
/**
 *
 * @package tests
 */
class testInstall extends zcCommonTestResources
{
  public function testInstallDo()
  {
    if (file_exists(DIR_FS_ADMIN . 'includes/local/configure.php'))
      unlink(DIR_FS_ADMIN . 'includes/local/configure.php');
    if (file_exists(DIR_FS_CATALOG . 'includes/local/configure.php'))
      unlink(DIR_FS_CATALOG . 'includes/local/configure.php');

    $this->open('http://' . BASE_URL);
    $this->waitForPageToLoad(10000);
    $this->assertTitle('System Setup Required');
    $this->open('http://' . BASE_URL . 'zc_install/');
    $this->waitForPageToLoad(10000);
    $this->assertTextPresent('glob:*Setup - Welcome*');
    $this->clickAndWait('submit');
    $this->assertTextPresent('glob:*License Confirmation*');
    $this->click('agree');
    $this->clickAndWait('submit');
    $this->assertTextPresent('glob:*System Inspection*');
    $this->clickAndWait('submit');
    $this->assertTextPresent('glob:*Database Setup*');
    $this->type('db_host', DB_HOST);
    $this->type('db_username', DB_USER);
    $this->type('db_pass', DB_PASS);
    $this->type('db_name', DB_DBNAME);
    $this->type('db_prefix', DB_PREFIX);
    $this->click('submit');
    $this->waitForPageToLoad(50000);
    $this->assertTextPresent('glob:*System Setup*');
    $this->clickAndWait('submit');
    $this->assertTextPresent('glob:*Store Setup*');
    $this->type('store_name', WEBTEST_STORE_NAME);
    $this->type('store_owner', WEBTEST_STORE_OWNER);
    $this->type('store_owner_email', WEBTEST_STORE_OWNER_EMAIL);
    $this->select('store_zone', 'value=18');
    $this->click('demo_install_yes');
    $this->clickAndWait('submit');
    $this->assertTextPresent('glob:*Administrator Account Setup*');
    $this->type('admin_username', WEBTEST_ADMIN_NAME_INSTALL);
    $this->type('admin_pass', WEBTEST_ADMIN_PASSWORD_INSTALL);
    $this->type('admin_pass_confirm', WEBTEST_ADMIN_PASSWORD_INSTALL);
    $this->type('admin_email', WEBTEST_ADMIN_EMAIL);
    $this->clickAndWait('submit');
    $this->assertTextPresent('glob:*Setup Finished*');
  }

  function testLoadStoreMainPage()
  {
    $this->open('http://' . BASE_URL);
    $this->waitForPageToLoad(10000);
    $this->assertTitle('Zen Cart!, The Art of E-commerce');
    $this->assertTextPresent('glob:*' . WEBTEST_STORE_NAME . '*');
  }

  function testLoadAdminMainPage()
  {
    $this->open('http://' . BASE_URL . 'admin/index.php');
    $this->waitForPageToLoad(10000);
    $this->assertTitle('Zen Cart!');
    $this->assertTextPresent('glob:*Admin Username*');
    $this->type("admin_name", WEBTEST_ADMIN_NAME_INSTALL);
    $this->type("admin_pass", WEBTEST_ADMIN_PASSWORD_INSTALL);
    $this->clickAndWait("submit");
    $this->type('admin_name', WEBTEST_ADMIN_NAME_INSTALL);
    $this->type('old_pwd', WEBTEST_ADMIN_PASSWORD_INSTALL);
    $this->type('admin_pass', WEBTEST_ADMIN_PASSWORD_INSTALL_1);
    $this->type('admin_pass2', WEBTEST_ADMIN_PASSWORD_INSTALL_1);
    $this->clickAndWait("submit");
    $this->assertTextPresent('glob:*Admin Home*');
  }

  function testEnablingHtmlMimeEmail()
  {
    $this->open('http://' . BASE_URL . 'admin/index.php');
    $this->waitForPageToLoad(10000);
    $this->assertTitle('Zen Cart!');
    $this->assertTextPresent('glob:*Admin Username*');
    $this->type("admin_name", WEBTEST_ADMIN_NAME_INSTALL);
    $this->type("admin_pass", WEBTEST_ADMIN_PASSWORD_INSTALL_1);
    $this->clickAndWait("submit");
    $this->clickAndWait("link=Developers Tool Kit");
    $this->assertEquals("Zen Cart!", $this->getTitle());
    $this->type("configuration_key", "EMAIL_USE_HTML");
    $this->clickAndWait("//input[@type='image']");
    $this->assertEquals("Zen Cart!", $this->getTitle());
    $this->clickAndWait("//img[@alt='Edit']");
    $this->clickAndWait("//img[@alt='Edit']");
    $this->click("true-configuration_value");
    $this->clickAndWait("submitEMAIL_USE_HTML");
    $this->assertEquals("Zen Cart!", $this->getTitle());
  }

  function testSettingHtmlEmailsAsDefault()
  {
    $this->open('http://' . BASE_URL . 'admin/index.php');
    $this->waitForPageToLoad(10000);
    $this->assertTitle('Zen Cart!');
    $this->assertTextPresent('glob:*Admin Username*');
    $this->type("admin_name", WEBTEST_ADMIN_NAME_INSTALL);
    $this->type("admin_pass", WEBTEST_ADMIN_PASSWORD_INSTALL_1);
    $this->clickAndWait("submit");
    $this->clickAndWait("link=Developers Tool Kit");
    $this->assertEquals("Zen Cart!", $this->getTitle());
    $this->type("configuration_key", "ADMIN_EXTRA_EMAIL_FORMAT");
    $this->clickAndWait("//input[@type='image']");
    $this->assertEquals("Zen Cart!", $this->getTitle());
    $this->clickAndWait("//img[@alt='Edit']");
    $this->clickAndWait("//img[@alt='Edit']");
    $this->click("html-configuration_value");
    $this->clickAndWait("submitADMIN_EXTRA_EMAIL_FORMAT");
    $this->assertEquals("Zen Cart!", $this->getTitle());
  }

  function testResetEmailServerToSMTPAUTH()
  {
    if (defined('WEBTEST_USE_SMTP') && WEBTEST_USE_SMTP == true)
    {
      $this->doDbQuery("UPDATE " . DB_PREFIX . "configuration set configuration_value = 'smtpauth' where configuration_key = 'EMAIL_TRANSPORT'");
      $this->doDbQuery("UPDATE " . DB_PREFIX . "configuration set configuration_value = " . WEBTEST_EMAIL_SMTPAUTH_MAILBOX . " where configuration_key = 'EMAIL_SMTPAUTH_MAILBOX'");
      $this->doDbQuery("UPDATE " . DB_PREFIX . "configuration set configuration_value = " . WEBTEST_EMAIL_SMTPAUTH_MAIL_SERVER . " where configuration_key = 'EMAIL_SMTPAUTH_MAIL_SERVER'");
      $this->doDbQuery("UPDATE " . DB_PREFIX . "configuration set configuration_value = " . WEBTEST_EMAIL_SMTPAUTH_PASSWORD . " where configuration_key = 'EMAIL_SMTPAUTH_PASSWORD'");
      $this->doDbQuery("UPDATE " . DB_PREFIX . "configuration set configuration_value = " . WEBTEST_EMAIL_SMTPAUTH_MAIL_SERVER_PORT . " where configuration_key = 'EMAIL_SMTPAUTH_MAIL_SERVER_PORT'");
      $this->doDbQuery("UPDATE " . DB_PREFIX . "configuration set configuration_value = " . WEBTEST_EMAIL_LINEFEED . " where configuration_key = 'EMAIL_LINEFEED'");

      //      $this->doDbQuery("update " . DB_PREFIX . "configuration set configuration_value = '' where configuration_key in ('STORE_OWNER_EMAIL_ADDRESS', 'EMAIL_FROM', 'SEND_EXTRA_ORDER_EMAILS_TO', 'SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO', 'SEND_EXTRA_LOW_STOCK_EMAILS_TO', 'SEND_EXTRA_GV_CUSTOMER_EMAILS_TO', 'SEND_EXTRA_GV_ADMIN_EMAILS_TO', 'SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO', 'SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO', 'SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO', 'MODULE_PAYMENT_CC_EMAIL', 'EMAIL_SYSTEMALERTS_ADDRESS', 'EMAIL_BOUNCE_ADDRESS') or configuration_key like 'SEND\_EXTRA%EMAILS\_TO'");
      //      $this->doDbQuery("update " . DB_PREFIX . "configuration set configuration_value = '1' where configuration_key in ('SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO_STATUS', 'SEND_EXTRA_GV_CUSTOMER_EMAILS_TO_STATUS', 'SEND_EXTRA_GV_ADMIN_EMAILS_TO_STATUS', 'SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO_STATUS', 'SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO_STATUS', 'SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO_STATUS', 'SEND_LOWSTOCK_EMAIL')");
      //      $this->doDbQuery("UPDATE " . DB_PREFIX . "configuration set configuration_value = '' where configuration_key = 'CONTACT_US_LIST'");
    } else
    {
      $this->markTestSkipped('Skipping SMTP setup');
    }
  }

  public function testSetupVAT()
  {
    $this->open('http://' . BASE_URL . 'admin/login.php');
    $this->waitForPageToLoad(10000);
    $this->type("admin_name", WEBTEST_ADMIN_NAME_INSTALL);
    $this->type("admin_pass", WEBTEST_ADMIN_PASSWORD_INSTALL_1);
    $this->clickAndWait("submit");
    $this->clickAndWait("link=Zones Definitions");
    $this->clickAndWait("//img[@alt='Insert']");
    $this->type("geo_zone_name", "UK/VAT");
    $this->clickAndWait("//input[@type='image']");
    $this->clickAndWait("//img[@alt='Details']");

    $this->clickAndWait("//img[@alt='Insert']");
    $this->select("zone_country_id", "label=United Kingdom");
    $this->clickAndWait("//input[@type='image']");

    $this->clickAndWait("//img[@alt='Insert']");
    $this->select("zone_country_id", "label=Ireland");
    $this->clickAndWait("//input[@type='image']");

    $this->clickAndWait("link=Tax Rates");
    $this->clickAndWait("//img[@alt='New Tax Rate']");
    $this->select("tax_zone_id", "label=UK/VAT");
    $this->type("tax_rate", "17.5");
    $this->type("tax_description", "VAT 17.5%");
    $this->type("tax_priority", "1");
    $this->clickAndWait("//input[@type='image']");
    $this->clickAndWait("link=Admin Home");
    $this->clickAndWait("link=Logoff");
  }

  public function testAddACaliforniaTax()
  {
    $this->open('http://' . BASE_URL . 'admin/login.php');
    $this->waitForPageToLoad(10000);
    $this->type("admin_name", WEBTEST_ADMIN_NAME_INSTALL);
    $this->type("admin_pass", WEBTEST_ADMIN_PASSWORD_INSTALL_1);
    $this->clickAndWait("submit");
    $this->clickAndWait("link=Zones Definitions");
    $this->clickAndWait("//img[@alt='Insert']");
    $this->type("geo_zone_name", "California");
    $this->clickAndWait("//input[@type='image']");
    $this->clickAndWait("//img[@alt='Details']");

    $this->clickAndWait("//img[@alt='Insert']");
    $this->select("zone_country_id", "label=United States");
    $this->select("zone_id", "label=California");
    $this->clickAndWait("//input[@type='image']");

    $this->clickAndWait("link=Tax Rates");
    $this->clickAndWait("//img[@alt='New Tax Rate']");
    $this->select("tax_zone_id", "label=California");
    $this->type("tax_rate", "12.75");
    $this->type("tax_description", "CA TAX 12.75%");
    $this->type("tax_priority", "1");
    $this->clickAndWait("//input[@type='image']");
    $this->clickAndWait("link=Admin Home");
    $this->clickAndWait("link=Logoff");
  }

  public function testAddPostageTax()
  {
    $this->open('http://' . BASE_URL . 'admin/login.php');
    $this->waitForPageToLoad(10000);
    $this->type("admin_name", WEBTEST_ADMIN_NAME_INSTALL);
    $this->type("admin_pass", WEBTEST_ADMIN_PASSWORD_INSTALL_1);
    $this->clickAndWait("submit");
    $this->open('http://' . BASE_URL . 'admin/tax_classes.php?page=1&action=new');
    $this->waitForPageToLoad(10000);
    $this->type("tax_class_title", "Taxable Postage");
    $this->type("tax_class_description", "Taxable Postage");
    $this->clickAndWait("//input[@type='image']");
    $this->clickAndWait("link=Admin Home");
    $this->clickAndWait("link=Logoff");
    $this->open('http://' . BASE_URL . 'admin/login.php');
    $this->waitForPageToLoad(10000);
    $this->type("admin_name", WEBTEST_ADMIN_NAME_INSTALL);
    $this->type("admin_pass", WEBTEST_ADMIN_PASSWORD_INSTALL_1);
    $this->clickAndWait("submit");
    $this->open('http://' . BASE_URL . 'admin/tax_rates.php?page=1&action=new');
    $this->waitForPageToLoad(10000);
    $this->select("tax_class_id", "label=Taxable Postage");
    $this->select("tax_zone_id", "label=Florida");
    $this->type("tax_rate", "19.00");
    $this->type("tax_description", "POSTAGE TAX 19%");
    $this->type("tax_priority", "1");
    $this->clickAndWait("//input[@type='image']");
    $this->clickAndWait("link=Admin Home");
    $this->clickAndWait("link=Logoff");
  }

  public function testSetupPreferredDefaults()
  {
    /**
     * Enable all credit card types
     */
    $this->doDbQuery("UPDATE " . DB_PREFIX . "configuration SET configuration_value = '1' where configuration_key RLIKE 'CC_ENABLED'");

    // set a shipping ZIP code -- needed by shipping modules such as USPS
    $this->doDbQuery("UPDATE " . DB_PREFIX . "configuration SET configuration_value = '90210' WHERE configuration_key='SHIPPING_ORIGIN_ZIP'");

    //change multiple-add-to-cart to just Buy Now:
    //$this->doDbQuery("UPDATE configuration SET configuration_value = '0' WHERE configuration_key='PRODUCT_LISTING_MULTIPLE_ADD_TO_CART'");

    // turn on the configuration-key display for easier finding of things for debugging
    $this->doDbQuery("UPDATE " . DB_PREFIX . "configuration SET configuration_value = 1 WHERE configuration_key='ADMIN_CONFIGURATION_KEY_ON'");
    //
    $this->doDbQuery("UPDATE " . DB_PREFIX . "configuration SET configuration_value = '200.00' WHERE configuration_key='MODULE_SHIPPING_STOREPICKUP_COST'");
    $this->doDbQuery("UPDATE " . DB_PREFIX . "configuration SET configuration_value = '100.00' WHERE configuration_key='MODULE_SHIPPING_ITEM_COST'");
  }

  public function testSetupCodModule()
  {
    $this->open('http://' . BASE_URL . 'admin/login.php');
    $this->waitForPageToLoad(10000);
    $this->type("admin_name", WEBTEST_ADMIN_NAME_INSTALL);
    $this->type("admin_pass", WEBTEST_ADMIN_PASSWORD_INSTALL_1);
    $this->clickAndWait("submit");
    $this->open('http://' . BASE_URL . 'admin/modules.php?set=payment&module=cod&action=remove');
    $this->waitForPageToLoad(10000);
    $this->clickAndWait("//input[@name='removeButton']");
    $this->open('http://' . BASE_URL . 'admin/modules.php?set=payment&module=cod');
    $this->waitForPageToLoad(10000);
    $this->clickAndWait("//input[@name='installButton']");
    $this->assertEquals("Zen Cart!", $this->getTitle());
    $this->clickAndWait("link=Admin Home");
    $this->clickAndWait("link=Logoff");
  }

}
