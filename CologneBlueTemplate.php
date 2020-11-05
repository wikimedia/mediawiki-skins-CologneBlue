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
 */

/**
 * @ingroup Skins
 */
class CologneBlueTemplate extends BaseTemplate {

	/**
	 * Run the skin and build html
	 */
	function execute() {
		// Suppress warnings to prevent notices about missing indexes in $this->data
		wfSuppressWarnings();
		$this->html( 'headelement' );
		echo $this->beforeContent();
		$this->html( 'bodytext' );
		echo "\n";
		echo $this->afterContent();
		$this->html( 'dataAfterContent' );
		$this->printTrail();
		echo "\n</body></html>";
		wfRestoreWarnings();
	}

	/**
	 * Language/charset variant links for classic-style skins
	 * @return string
	 */
	function variantLinks() {
		$s = [];

		$variants = $this->data['content_navigation']['variants'];

		foreach ( $variants as $key => $link ) {
			$s[] = $this->makeListItem( $key, $link, [ 'tag' => 'span' ] );
		}

		return $this->getSkin()->getLanguage()->pipeList( $s );
	}

	/**
	 * @return string
	 */
	function otherLanguages() {
		if ( $this->config->get( 'HideInterlanguageLinks' ) ) {
			return "";
		}

		$html = '';

		// We override SkinTemplate->formatLanguageName() in SkinCologneBlue
		// not to capitalize the language names.
		$language_urls = $this->data['language_urls'];
		if ( !empty( $language_urls ) ) {
			$s = [];
			foreach ( $language_urls as $key => $data ) {
				$s[] = $this->makeListItem( $key, $data, [ 'tag' => 'span' ] );
			}

			$html = wfMessage( 'otherlanguages' )->text()
				. wfMessage( 'colon-separator' )->text()
				. $this->getSkin()->getLanguage()->pipeList( $s );
		}

		$html .= $this->renderAfterPortlet( 'lang' );

		return $html;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	protected function renderAfterPortlet( $name ) {
		$content = '';
		Hooks::run( 'BaseTemplateAfterPortlet', [ $this, $name, &$content ] );

		$html = $content !== '' ? "<div class='after-portlet after-portlet-$name'>$content</div>" : '';

		return $html;
	}

	/**
	 * @return string
	 */
	function pageTitleLinks() {
		$s = [];
		$footlinks = $this->getFooterLinks();

		foreach ( $footlinks['places'] as $item ) {
			$s[] = $this->data[$item];
		}

		return $this->getSkin()->getLanguage()->pipeList( $s );
	}

	/**
	 * Used in bottomLinks() to eliminate repetitive code.
	 *
	 * @param string $key Key to be passed to makeListItem()
	 * @param array $navlink Navlink suitable for processNavlinkForDocument()
	 * @param string $message Key of the message to use in place of standard text
	 *
	 * @return string
	 */
	function processBottomLink( $key, $navlink, $message = null ) {
		if ( !$navlink ) {
			// Empty navlinks might be passed.
			return null;
		}

		if ( $message ) {
			$navlink['text'] = wfMessage( $message )->escaped();
		}

		return $this->makeListItem(
			$key,
			$this->processNavlinkForDocument( $navlink ),
			[ 'tag' => 'span' ]
		);
	}

	/**
	 * @return string
	 */
	function bottomLinks() {
		$toolbox = $this->getToolbox();
		$content_nav = $this->data['content_navigation'];

		$lines = [];

		if ( $this->getSkin()->getOutput()->isArticleRelated() ) {
			// First row. Regular actions.
			$element = [];

			$editLinkMessage = $this->getSkin()->getTitle()->exists() ? 'editthispage' : 'create-this-page';
			$element[] = $this->processBottomLink( 'edit', $content_nav['views']['edit'], $editLinkMessage );
			$element[] = $this->processBottomLink(
				'viewsource',
				$content_nav['views']['viewsource'],
				'viewsource'
			);

			$element[] = $this->processBottomLink(
				'watch',
				$content_nav['actions']['watch'],
				'watchthispage'
			);
			$element[] = $this->processBottomLink(
				'unwatch',
				$content_nav['actions']['unwatch'],
				'unwatchthispage'
			);

			$element[] = $this->talkLink();

			$element[] = $this->processBottomLink( 'history', $content_nav['views']['history'], 'history' );
			$element[] = $this->processBottomLink( 'info', $toolbox['info'] );
			$element[] = $this->processBottomLink( 'whatlinkshere', $toolbox['whatlinkshere'] );
			$element[] = $this->processBottomLink( 'recentchangeslinked', $toolbox['recentchangeslinked'] );

			$element[] = $this->processBottomLink( 'contributions', $toolbox['contributions'] );
			if ( isset( $toolbox['emailuser'] ) ) {
				$element[] = $this->processBottomLink( 'emailuser', $toolbox['emailuser'] );
			}

			$lines[] = $this->getSkin()->getLanguage()->pipeList( array_filter( $element ) );

			// Second row. Privileged actions.
			$element = [];

			$element[] = $this->processBottomLink(
				'delete',
				$content_nav['actions']['delete'],
				'deletethispage'
			);
			if ( isset( $content_nav['actions']['undelete'] ) ) {
				$element[] = $this->processBottomLink(
					'undelete',
					$content_nav['actions']['undelete'],
					'undeletethispage'
				);
			}

			if ( isset( $content_nav['actions']['protect'] ) ) {
				$element[] = $this->processBottomLink(
					'protect',
					$content_nav['actions']['protect'],
					'protectthispage'
				);
			}

			if ( isset( $content_nav['actions']['unprotect'] ) ) {
				$element[] = $this->processBottomLink(
					'unprotect',
					$content_nav['actions']['unprotect'],
					'unprotectthispage'
				);
			}

			$element[] = $this->processBottomLink( 'move', $content_nav['actions']['move'], 'movethispage' );

			$lines[] = $this->getSkin()->getLanguage()->pipeList( array_filter( $element ) );

			// Third row. Language links.
			$lines[] = $this->otherLanguages();
		}

		return implode( array_filter( $lines ), "<br />\n" ) . "<br />\n";
	}

	/**
	 * @return string
	 */
	function talkLink() {
		$title = $this->getSkin()->getTitle();

		if ( $title->getNamespace() == NS_SPECIAL ) {
			// No discussion links for special pages
			return "";
		}

		$companionTitle = $title->isTalkPage() ? $title->getSubjectPage() : $title->getTalkPage();
		$companionNamespace = $companionTitle->getNamespace();

		// TODO these messages are only be used by CologneBlue,
		// kill and replace with something more sensibly named?
		$nsToMessage = [
			NS_MAIN => 'articlepage',
			NS_USER => 'userpage',
			NS_PROJECT => 'projectpage',
			NS_FILE => 'imagepage',
			NS_MEDIAWIKI => 'mediawikipage',
			NS_TEMPLATE => 'templatepage',
			NS_HELP => 'viewhelppage',
			NS_CATEGORY => 'categorypage',
			NS_FILE => 'imagepage',
		];

		// Find out the message to use for link text. Use either the array above or,
		// for non-talk pages, a generic "discuss this" message.
		// Default is the same as for main namespace.
		if ( isset( $nsToMessage[$companionNamespace] ) ) {
			$message = $nsToMessage[$companionNamespace];
		} else {
			$message = $companionTitle->isTalkPage() ? 'talkpage' : 'articlepage';
		}

		// Obviously this can't be reasonable and just return the key for talk
		// namespace, only for content ones. Thus we have to mangle it in
		// exactly the same way SkinTemplate does. (bug 40805)
		$key = $companionTitle->getNamespaceKey( '' );
		if ( $companionTitle->isTalkPage() ) {
			$key = ( $key == 'main' ? 'talk' : $key . "_talk" );
		}

		// Use the regular navigational link, but replace its text. Everything else stays unmodified.
		$namespacesLinks = $this->data['content_navigation']['namespaces'];

		return $this->processBottomLink( $message, $namespacesLinks[$key], $message );
	}

	/**
	 * Takes a navigational link generated by SkinTemplate in whichever way
	 * and mangles attributes unsuitable for repeated use. In particular, this
	 * modifies the ids and removes the accesskeys. This is necessary to be
	 * able to use the same navlink twice, e.g. in sidebar and in footer.
	 *
	 * @param array $navlink Navigational link generated by SkinTemplate
	 * @param mixed $idPrefix Prefix to add to id of this navlink. If false, id
	 *   is removed entirely. Default is 'cb-'.
	 * @return array
	 */
	function processNavlinkForDocument( $navlink, $idPrefix = 'cb-' ) {
		if ( $navlink['id'] ) {
			$navlink['single-id'] = $navlink['id']; // to allow for tooltip generation
			$navlink['tooltiponly'] = true; // but no accesskeys

			// mangle or remove the id
			if ( $idPrefix === false ) {
				unset( $navlink['id'] );
			} else {
				$navlink['id'] = $idPrefix . $navlink['id'];
			}
		}

		return $navlink;
	}

	/**
	 * @return string
	 */
	function beforeContent() {
		ob_start();
		?>
		<div id="content">
		<div id="topbar">
			<p id="sitetitle" role="banner">
				<a href="<?php echo htmlspecialchars( $this->data['nav_urls']['mainpage']['href'] ) ?>">
					<?php echo wfMessage( 'sitetitle' )->escaped() ?>
				</a>
			</p>

			<p id="sitesub"><?php echo wfMessage( 'sitesubtitle' )->escaped() ?></p>

			<div id="linkcollection" role="navigation">
				<div id="langlinks"><?php echo str_replace( '<br />', '', $this->otherLanguages() ) ?></div>
				<?php echo $this->getSkin()->getCategories() ?>
				<div id="titlelinks"><?php echo $this->pageTitleLinks() ?></div>
				<?php
				if ( $this->data['newtalk'] ) {
					?>
					<div class="usermessage"><strong><?php echo $this->data['newtalk'] ?></strong></div>
				<?php
				}
				?>
			</div>
		</div>
		<div id="article" class="mw-body" role="main">
		<?php
		if ( $this->getSkin()->getSiteNotice() ) {
			?>
			<div id="siteNotice"><?php echo $this->getSkin()->getSiteNotice() ?></div>
		<?php
		}
		?>
		<?php echo $this->getIndicators(); ?>
		<h1 id="firstHeading" lang="<?php
		$this->data['pageLanguage'] = $this->getSkin()->getTitle()->getPageViewLanguage()->getHtmlCode();
		$this->text( 'pageLanguage' );
		?>"><?php echo $this->data['title'] ?></h1>
		<?php
		if ( wfMessage( 'tagline' )->text() ) {
			?>
			<p class="tagline"><?php
				echo htmlspecialchars( wfMessage( 'tagline' )->text() )
				?></p>
		<?php
		}
		?>
		<?php
		if ( $this->getSkin()->getOutput()->getSubtitle() ) {
			?>
			<p class="subtitle"><?php echo $this->getSkin()->getOutput()->getSubtitle() ?></p>
		<?php
		}
		?>
		<?php
		if ( $this->getSkin()->subPageSubtitle() ) {
			?>
			<p class="subpages"><?php echo $this->getSkin()->subPageSubtitle() ?></p>
		<?php
		}
		?>
		<?php
		$s = ob_get_contents();
		ob_end_clean();

		return $s;
	}

	/**
	 * @return string
	 */
	function afterContent() {
		ob_start();
		?>
		</div>
		<div id="footer">
			<div id="footer-navigation" role="navigation">
				<?php
				// Page-related links
				echo $this->bottomLinks();
				echo "\n<br />";

				// Footer and second searchbox
				echo $this->getSkin()->getLanguage()->pipeList( [
					$this->getSkin()->mainPageLink(),
					$this->getSkin()->aboutLink(),
					$this->searchForm( 'footer' )
				] );
				?>
			</div>
			<div id="footer-info" role="contentinfo">
				<?php
				// Standard footer info
				$footlinks = $this->getFooterLinks();
				if ( $footlinks['info'] ) {
					foreach ( $footlinks['info'] as $item ) {
						echo $this->data[$item] . ' ';
					}
				}
				?>
			</div>
		</div>
		</div>
		<div id="mw-navigation">
			<h2><?php echo wfMessage( 'navigation-heading' )->escaped() ?></h2>

			<div id="toplinks" role="navigation">
				<p id="syslinks"><?php echo $this->sysLinks() ?></p>

				<p id="variantlinks"><?php echo $this->variantLinks() ?></p>
			</div>
			<?php echo $this->quickBar() ?>
		</div>
		<?php
		$s = ob_get_contents();
		ob_end_clean();

		return $s;
	}

	/**
	 * @return string
	 */
	function sysLinks() {
		$s = [
			$this->getSkin()->mainPageLink(),
			Linker::linkKnown(
				Title::newFromText( wfMessage( 'aboutpage' )->inContentLanguage()->text() ),
				wfMessage( 'about' )->text()
			),
			Linker::makeExternalLink(
				Skin::makeInternalOrExternalUrl( wfMessage( 'helppage' )->inContentLanguage()->text() ),
				wfMessage( 'help' )->text(),
				false
			),
			Linker::linkKnown(
				Title::newFromText( wfMessage( 'faqpage' )->inContentLanguage()->text() ),
				wfMessage( 'faq' )->text()
			),
		];

		$personalUrls = $this->getPersonalTools();
		foreach ( [ 'logout', 'createaccount', 'login' ] as $key ) {
			if ( isset( $personalUrls[$key] ) ) {
				$s[] = $this->makeListItem( $key, $personalUrls[$key], [ 'tag' => 'span' ] );
			}
		}

		return $this->getSkin()->getLanguage()->pipeList( $s );
	}

	/**
	 * Adds CologneBlue-specific items to the sidebar: qbedit, qbpageoptions and qbmyoptions menus.
	 *
	 * @param array $bar Sidebar data
	 * @return array Modified sidebar data
	 */
	function sidebarAdditions( $bar ) {
		// "This page" and "Edit" menus
		// We need to do some massaging here... we reuse all of the items,
		// except for $...['views']['view'], as $...['namespaces']['main'] and
		// $...['namespaces']['talk'] together serve the same purpose. We also
		// don't use $...['variants'], these are displayed in the top menu.
		$content_navigation = $this->data['content_navigation'];
		$qbpageoptions = array_merge(
			$content_navigation['namespaces'],
			[
				'history' => $content_navigation['views']['history'],
				'watch' => $content_navigation['actions']['watch'],
				'unwatch' => $content_navigation['actions']['unwatch'],
			]
		);
		$content_navigation['actions']['watch'] = null;
		$content_navigation['actions']['unwatch'] = null;
		$qbEditLinks = [ 'edit' => $content_navigation['views']['edit'] ];
		if ( isset( $content_navigation['views']['addsection'] ) ) {
			$qbEditLinks['addsection'] = $content_navigation['views']['addsection'];
		}
		$qbedit = array_merge(
			$qbEditLinks,
			$content_navigation['actions']
		);

		// Personal tools ("My pages")
		$qbmyoptions = $this->getPersonalTools();
		foreach ( [ 'logout', 'createaccount', 'login' ] as $key ) {
			$qbmyoptions[$key] = null;
		}

		// Use the closest reasonable name
		$bar['cactions'] = $qbedit;
		$bar['pageoptions'] = $qbpageoptions; // this is a non-standard portlet name, but nothing fits
		$bar['personal'] = $qbmyoptions;

		return $bar;
	}

	/**
	 * Compute the sidebar
	 * @access private
	 *
	 * @return string
	 */
	function quickBar() {
		// Massage the sidebar. We want to:
		// * place SEARCH at the beginning
		// * add new portlets before TOOLBOX (or at the end, if it's missing)
		// * remove LANGUAGES (langlinks are displayed elsewhere)
		$orig_bar = $this->data['sidebar'];
		$bar = [];
		$hasToolbox = false;

		// Always display search first
		$bar['SEARCH'] = true;
		// Copy everything except for langlinks, inserting new items before toolbox
		foreach ( $orig_bar as $heading => $data ) {
			if ( $heading == 'TOOLBOX' ) {
				// Insert the stuff
				$bar = $this->sidebarAdditions( $bar );
				$hasToolbox = true;
			}

			if ( $heading != 'LANGUAGES' ) {
				$bar[$heading] = $data;
			}
		}
		// If toolbox is missing, add our items at the end
		if ( !$hasToolbox ) {
			$bar = $this->sidebarAdditions( $bar );
		}

		// Fill out special sidebar items with content
		$orig_bar = $bar;
		$bar = [];
		foreach ( $orig_bar as $heading => $data ) {
			if ( $heading == 'SEARCH' ) {
				$bar['search'] = $this->searchForm( 'sidebar' );
			} elseif ( $heading == 'TOOLBOX' ) {
				$bar['tb'] = $this->getToolbox();
			} else {
				$bar[$heading] = $data;
			}
		}

		// Output the sidebar
		// CologneBlue uses custom messages for some portlets, but we should keep the ids for consistency
		$idToMessage = [
			'search' => 'qbfind',
			'navigation' => 'qbbrowse',
			'tb' => 'toolbox',
			'cactions' => 'qbedit',
			'personal' => 'qbmyoptions',
			'pageoptions' => 'qbpageoptions',
		];

		$s = "<div id='quickbar'>\n";

		foreach ( $bar as $heading => $data ) {
			// Numeric strings gets an integer when set as key, cast back - T73639
			$heading = (string)$heading;

			$portletId = Sanitizer::escapeId( "p-$heading" );
			$headingMsg = wfMessage( $idToMessage[$heading] ? $idToMessage[$heading] : $heading );
			$headingHTML = "<h3>";
			$headingHTML .= $headingMsg->exists()
				? $headingMsg->escaped()
				: htmlspecialchars( $heading );
			$headingHTML .= "</h3>";
			$listHTML = "";

			if ( is_array( $data ) ) {
				// $data is an array of links
				foreach ( $data as $key => $link ) {
					// Can be empty due to how the sidebar additions are done
					if ( $link ) {
						$listHTML .= $this->makeListItem( $key, $link );
					}
				}
				if ( $listHTML ) {
					$listHTML = "<ul>$listHTML</ul>";
				}
			} else {
				// $data is a HTML <ul>-list string
				$listHTML = $data;
			}

			if ( $listHTML ) {
				$role = ( $heading == 'search' ) ? 'search' : 'navigation';
				$s .= "<div class=\"portlet\" id=\"$portletId\" "
					. "role=\"$role\">\n$headingHTML\n$listHTML\n</div>\n";
			}

			$s .= $this->renderAfterPortlet( $heading );
		}

		$s .= "</div>\n";

		return $s;
	}

	/**
	 * @param string $which
	 * @return string
	 */
	function searchForm( $which ) {
		$search = $this->getSkin()->getRequest()->getText( 'search' );
		$action = $this->data['searchaction'];
		$s = "<form id=\"searchform-" . htmlspecialchars( $which )
			. "\" method=\"get\" class=\"inline\" action=\"$action\">";
		if ( $which == 'footer' ) {
			$s .= wfMessage( 'qbfind' )->escaped() . ": ";
		}

		$s .= $this->makeSearchInput( [
			'class' => 'mw-searchInput',
			'type' => 'text',
			'size' => '14'
		] );
		$s .= ( $which == 'footer' ? " " : "<br />" );
		$s .= $this->makeSearchButton( 'go', [ 'class' => 'searchButton' ] );

		if ( $this->config->get( 'UseTwoButtonsSearchForm' ) ) {
			$s .= $this->makeSearchButton( 'fulltext', [ 'class' => 'searchButton' ] );
		} else {
			$s .= '<div><a href="' . $action . '" rel="search">'
				. wfMessage( 'powersearch-legend' )->escaped() . "</a></div>\n";
		}

		$s .= '</form>';

		return $s;
	}
}
