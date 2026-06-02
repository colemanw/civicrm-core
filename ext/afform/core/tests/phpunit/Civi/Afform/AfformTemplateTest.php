<?php
namespace Civi\Afform;

use Civi\Api4\AfformTemplate;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * @group headless
 */
class AfformTemplateTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, TransactionalInterface {

  private $templateName = 'test_template_123';

  public function setUpHeadless() {
    return \Civi\Test::headless()->installMe(__DIR__)->install('org.civicrm.search_kit')->install('org.civicrm.afform_admin')->apply();
  }

  public function tearDown(): void {
    AfformTemplate::revert(FALSE)->addWhere('name', '=', $this->templateName)->execute();
    parent::tearDown();
  }

  public function testTemplateCrud() {
    // 1. Create a template
    AfformTemplate::create(FALSE)
      ->addValue('name', $this->templateName)
      ->addValue('title', 'My Test Template')
      ->addValue('layout', '<af-form><div>Template Layout</div></af-form>')
      ->execute();

    // 2. Get the template metadata and layout
    $result = AfformTemplate::get(FALSE)
      ->addWhere('name', '=', $this->templateName)
      ->addSelect('*', 'directive_name', 'module_name')
      ->setLayoutFormat('html')
      ->execute()->single();

    $this->assertEquals($this->templateName, $result['name']);
    $this->assertEquals('My Test Template', $result['title']);
    $this->assertStringContainsString('Template Layout', $result['layout']);
    $this->assertEquals('test-template-123', $result['directive_name']);
    $this->assertEquals('testTemplate123', $result['module_name']);
    $this->assertArrayHasKey('modified_date', $result);

    // 3. Update/Save the template
    AfformTemplate::update(FALSE)
      ->addWhere('name', '=', $this->templateName)
      ->addValue('title', 'My Updated Test Template')
      ->execute();

    $resultUpdated = AfformTemplate::get(FALSE)
      ->addWhere('name', '=', $this->templateName)
      ->execute()->single();

    $this->assertEquals('My Updated Test Template', $resultUpdated['title']);

    // 4. Revert/Delete the template
    AfformTemplate::revert(FALSE)
      ->addWhere('name', '=', $this->templateName)
      ->execute();

    $resultDeleted = AfformTemplate::get(FALSE)
      ->addWhere('name', '=', $this->templateName)
      ->execute();

    $this->assertCount(0, $resultDeleted);
  }

  public function testLoadFromTemplate() {
    // 1. Create a template
    AfformTemplate::create(FALSE)
      ->addValue('name', $this->templateName)
      ->addValue('title', 'My Test Template')
      ->addValue('type', 'form')
      ->addValue('layout', [['#tag' => 'af-form', 'ctrl' => 'afform', '#children' => []]])
      ->execute();

    // 2. Load admin data from the template for creating a new Afform
    $adminData = \civicrm_api4('Afform', 'loadAdminData', [
      'definition' => ['template' => $this->templateName],
    ])->first();

    $this->assertNotEmpty($adminData);
    $this->assertEquals('My Test Template', $adminData['definition']['title']);
    $this->assertEquals('form', $adminData['definition']['type']);
    // Name must be stripped
    $this->assertArrayNotHasKey('name', $adminData['definition']);
    $this->assertEquals([['#tag' => 'af-form', 'ctrl' => 'afform']], $adminData['definition']['layout']);
  }

}
