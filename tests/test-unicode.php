<?php

use Tarosky\Shouyaku\Unicode;

/**
 * Test unit test results.
 */
class UnicodeTest extends WP_UnitTestCase {
	
	/**
	 * Test calculator.
	 */
	public function test_calc() {
		$this->assertEquals( 0.5, Unicode::ratio( 'This4444', '\d' ) );
	}
	
	public function test_cjk() {
		
		$this->assertTrue( Unicode::is_cjk( '本日は晴天なり。' ) );
		$this->assertTrue( Unicode::is_cjk( '私はWordPressをインストールできます。' ) );
		$this->assertTrue( Unicode::is_cjk( '永别了，爸爸，愿您的灵魂得到安息。' ) );
		$this->assertTrue( Unicode::is_cjk( '我的工作单位为了纪念创立100周年制作了特制的领带别针。' ) );
		$this->assertTrue( Unicode::is_cjk( '그건 일본에 돌아가서도 할 수 있잖아요.' ) );
		
		$this->assertFalse( Unicode::is_cjk( 'It\'s fine today.' ) );
		$this->assertFalse( Unicode::is_cjk( 'I can install ワードプレス.' ) );
	}
}
