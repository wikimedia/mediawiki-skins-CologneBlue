<?php
/**
 * Cologne Blue: A nicer-looking alternative to Standard.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup Skins
 */

/**
 * @ingroup Skins
 */
class SkinCologneBlue extends SkinMustache {
	/**
	 * Add data for links
	 * @return array
	 */
	private function getLinksTemplateData() {
		$links = [
			'faq' => 'faqpage',
			'about' => 'aboutpage',
			'help' => 'helppage',
			'create-account' => 'Special:CreateAccount',
			'logout' => 'Special:UserLogout',
			'login' => 'Special:UserLogin',
		];

		$linkTemplateData = [];
		foreach ( $links as $key => $pageOrMessage ) {
			$msgObj = $this->msg( $pageOrMessage );
			if ( $msgObj->exists() ) {
				$url = Skin::makeInternalOrExternalUrl( $msgObj->inContentLanguage()->text() );
			} else {
				$url = Title::newFromText( $pageOrMessage )->getLocalURL();
			}
			$linkTemplateData['link-' . $key] = $url;
		}

		return $linkTemplateData;
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplateData() {
		$data = parent::getTemplateData();
		return $data + $this->getLinksTemplateData() + [
			'is-anon' => $this->getUser()->isAnon(),
		];
	}
}
