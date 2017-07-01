<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use wulaphp\i18n\I18n;

class I18nTest extends TestCase {
	public function testI18nZh() {
		define('LANGUAGE', 'zh');
		I18n::addLang(__DIR__);
		self::assertEquals('你好', __('hello'));
		self::assertEquals('嗨', _tr('hi@test'));
	}
}