<?php
/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines
 * @copyright 2014 Simple Machines and individual contributors
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.1 Alpha 1
 */

function template_main()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	if (!empty($context['boards']) && (!empty($options['show_children']) || $context['start'] == 0))
	{
		echo '
	<div id="board_', $context['current_board'], '_childboards" class="boardindex_table">
		<table class="table_list">
			<tbody class="header">
				<tr>
					<td colspan="4">
						<div class="cat_bar">
							<h3 class="catbg">', $txt['sub_boards'], '</h3>
						</div>
					</td>
				</tr>
			</tbody>
			<tbody id="board_', $context['current_board'], '_children" class="content">';

		foreach ($context['boards'] as $board)
		{
			echo '
				<tr id="board_', $board['id'], '" class="windowbg2">
					<td class="windowbg icon"', !empty($board['children']) ? ' rowspan="2"' : '', '>
						<a href="', ($board['is_redirect'] || $context['user']['is_guest'] ? $board['href'] : $scripturl . '?action=unread;board=' . $board['id'] . '.0;children'), '">
							<span class="board_', $board['board_class'], '"', !empty($board['board_tooltip']) ? ' title="' . $board['board_tooltip'] . '"' : '', '></span>
						</a>
					</td>
					<td class="info">
						<a class="subject" href="', $board['href'], '" id="b', $board['id'], '">', $board['name'], '</a>';

			// Has it outstanding posts for approval?
			if ($board['can_approve_posts'] && ($board['unapproved_posts'] || $board['unapproved_topics']))
				echo '
						<a href="', $scripturl, '?action=moderate;area=postmod;sa=', ($board['unapproved_topics'] > 0 ? 'topics' : 'posts'), ';brd=', $board['id'], ';', $context['session_var'], '=', $context['session_id'], '" title="', sprintf($txt['unapproved_posts'], $board['unapproved_topics'], $board['unapproved_posts']), '" class="moderation_link">(!)</a>';

			echo '
						<p>', $board['description'] , '</p>';

			// Show the "Moderators: ". Each has name, href, link, and id. (but we're gonna use link_moderators.)
			if (!empty($board['moderators']) || !empty($board['moderator_groups']))
				echo '
						<p class="moderators">', count($board['link_moderators']) === 1 ? $txt['moderator'] : $txt['moderators'], ': ', implode(', ', $board['link_moderators']), '</p>';

			// Show some basic information about the number of posts, etc.
			echo '
					</td>
					<td class="windowbg stats">
						<p>', comma_format($board['posts']), ' ', $board['is_redirect'] ? $txt['redirects'] : $txt['posts'], ' <br />
						', $board['is_redirect'] ? '' : comma_format($board['topics']) . ' ' . $txt['board_topics'], '
						</p>
					</td>
					<td class="lastpost">';

			if (!empty($board['last_post']['id']))
				echo '
						<p>', $board['last_post']['last_post_message'], '</p>';
			echo '
					</td>
				</tr>';

			// Show the "Child Boards: ". (there's a link_children but we're going to bold the new ones...)
			if (!empty($board['children']))
			{
				// Sort the links into an array with new boards bold so it can be imploded.
				$children = array();
				/* Each child in each board's children has:
						id, name, description, new (is it new?), topics (#), posts (#), href, link, and last_post. */
				foreach ($board['children'] as $child)
				{
					if (!$child['is_redirect'])
						$child['link'] = '<a href="' . $child['href'] . '" ' . ($child['new'] ? 'class="board_new_posts" ' : '') . 'title="' . ($child['new'] ? $txt['new_posts'] : $txt['old_posts']) . ' (' . $txt['board_topics'] . ': ' . comma_format($child['topics']) . ', ' . $txt['posts'] . ': ' . comma_format($child['posts']) . ')">' . $child['name'] . ($child['new'] ? '</a> <a  ' . ($child['new'] ? 'class="new_posts" ' : '') . 'href="' . $scripturl . '?action=unread;board=' . $child['id'] . '" title="' . $txt['new_posts'] . ' (' . $txt['board_topics'] . ': ' . comma_format($child['topics']) . ', ' . $txt['posts'] . ': ' . comma_format($child['posts']) . ')"><span class="new_posts">' . $txt['new'] . '</span>' : '') . '</a>';
					else
						$child['link'] = '<a href="' . $child['href'] . '" title="' . comma_format($child['posts']) . ' ' . $txt['redirects'] . '">' . $child['name'] . '</a>';

					// Has it posts awaiting approval?
					if ($child['can_approve_posts'] && ($child['unapproved_posts'] | $child['unapproved_topics']))
						$child['link'] .= ' <a href="' . $scripturl . '?action=moderate;area=postmod;sa=' . ($child['unapproved_topics'] > 0 ? 'topics' : 'posts') . ';brd=' . $child['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" title="' . sprintf($txt['unapproved_posts'], $child['unapproved_topics'], $child['unapproved_posts']) . '" class="moderation_link">(!)</a>';

					$children[] = $child['new'] ? '<strong>' . $child['link'] . '</strong>' : $child['link'];
				}

			echo '
				<tr id="board_', $board['id'], '_children" class="windowbg2">
					<td colspan="3" class="windowbg children">
						<p><strong>', $txt['sub_boards'], '</strong>: ', implode(', ', $children), '</p>
					</td>
				</tr>';
			}
		}
		echo '
			</tbody>
			<tbody class="divider">
				<tr>
					<td colspan="4"></td>
				</tr>
			</tbody>
		</table>
	</div>';
	}

	// They can only mark read if they are logged in and it's enabled!
	if (!$context['user']['is_logged'] || !$settings['show_mark_read'])
		unset($context['normal_buttons']['markread']);

	if (!$context['no_topic_listing'])
	{
		echo '
	<div class="pagesection">
		', !empty($modSettings['topbottomEnable']) ? $context['menu_separator'] . '<a href="#bot" class="topbottom floatleft">' . $txt['go_down'] . '</a>' : '', '
		<div class="pagelinks floatleft">', $context['page_index'], '</div>
		', template_button_strip($context['normal_buttons'], 'right'), '
	</div>';

	if ($context['description'] != '' || !empty($context['moderators']))
		{
		echo '
	<div id="description_board" class="generic_list_wrapper">
		<h3 class="floatleft">', $context['name'], '&nbsp;-&nbsp;</h3>
		<p>';
	if ($context['description'] != '')
	echo '
		', $context['description'], '&nbsp;';

	if (!empty($context['moderators']))
	echo '
		', count($context['moderators']) === 1 ? $txt['moderator'] : $txt['moderators'], ': ', implode(', ', $context['link_moderators']), '.';

	echo '
		</p>
	</div>';
		}

		// If Quick Moderation is enabled start the form.
		if (!empty($context['can_quick_mod']) && $options['display_quick_mod'] > 0 && !empty($context['topics']))
			echo '
	<form action="', $scripturl, '?action=quickmod;board=', $context['current_board'], '.', $context['start'], '" method="post" accept-charset="', $context['character_set'], '" class="clear" name="quickModForm" id="quickModForm">';

		echo '
		<div class="tborder topic_table" id="messageindex">';
		if (!empty($settings['display_who_viewing']))
		{
		echo '
			<p class="whoisviewing">';
			if ($settings['display_who_viewing'] == 1)
				echo count($context['view_members']), ' ', count($context['view_members']) === 1 ? $txt['who_member'] : $txt['members'];
		else
				echo empty($context['view_members_list']) ? '0 ' . $txt['members'] : implode(', ', $context['view_members_list']) . (empty($context['view_num_hidden']) || $context['can_moderate_forum'] ? '' : ' (+ ' . $context['view_num_hidden'] . ' ' . $txt['hidden'] . ')');
			echo $txt['who_and'], $context['view_num_guests'], ' ', $context['view_num_guests'] == 1 ? $txt['guest'] : $txt['guests'], $txt['who_viewing_board'];

		echo '
			</p>';
		}
	echo '
		<table class="table_grid">
			<thead>
				<tr class="catbg">';

		// Are there actually any topics to show?
		if (!empty($context['topics']))
		{
			echo '
					<th scope="col" class="post_icon first_th">&nbsp;</th>
					<th scope="col" class="subject lefttext">', $context['topics_headers']['subject'], ' / ', $context['topics_headers']['starter'], '</th>
					<th scope="col" class="stats">', $context['topics_headers']['replies'], ' / ', $context['topics_headers']['views'], '</th>';
			// Show a "select all" box for quick moderation?
			if (empty($context['can_quick_mod']))
				echo '
					<th scope="col" class="last_post lefttext last_th">', $context['topics_headers']['last_post'], '</th>';
			else
				echo '
					<th scope="col" class="last_post lefttext">', $context['topics_headers']['last_post'], '</th>';

			// Show a "select all" box for quick moderation?
			if (!empty($context['can_quick_mod']) && $options['display_quick_mod'] == 1)
				echo '
					<th scope="col" class="moderation last_th"><input type="checkbox" onclick="invertAll(this, this.form, \'topics[]\');" class="input_check" /></th>';

			// If it's on in "image" mode, don't show anything but the column.
			elseif (!empty($context['can_quick_mod']))
				echo '
					<th class="last_th" width="4%">&nbsp;</th>';
		}
		// No topics.... just say, "sorry bub".
		else
			echo '
					<th scope="col" class="first_th" width="8%">&nbsp;</th>
					<th colspan="3"><strong>', $txt['topic_alert_none'], '</strong></th>
					<th scope="col" class="last_th" width="8%">&nbsp;</th>';

		echo '
				</tr>
			</thead>
			<tbody>';

		// If this person can approve items and we have some awaiting approval tell them.
		if (!empty($context['unapproved_posts_message']))
		{
			echo '
				<tr class="windowbg2">
					<td colspan="', !empty($context['can_quick_mod']) ? '5' : '4', '">
						<span class="alert">!</span> ', $context['unapproved_posts_message'], '
					</td>
				</tr>';
		}

		foreach ($context['topics'] as $topic)
		{
			$color_class = 'windowbg';

			// Is this topic pending approval, or does it have any posts pending approval?
			if ($context['can_approve_posts'] && $topic['unapproved_posts'])
				$color_class = (!$topic['approved'] ? 'approvetopic ' : 'approvepost ') . $color_class;

			// Sticky topics should get a different color, too.
			if ($topic['is_sticky'])
				$color_class = 'sticky ' . $color_class;
			// Locked topics get special treatment as well.
			if ($topic['is_locked'])
				$color_class = 'locked ' . $color_class;

			// Some columns require a different shade of the color class.
			$alternate_class = $color_class . '2';

			// @todo - [WIP] Markup can be cleaned up later. CSS can go in the CSS files later.
			echo '
				<tr>
					<td class="', $color_class, ' icon2">
						<div>
							<img src="', $topic['first_post']['icon_url'], '" alt="" />
							', $topic['is_posted_in'] ? '<img class="posted" src="' . $settings['images_url'] . '/icons/profile_sm.png" alt="" />' : '', '
						</div>
					</td>
					<td class="', $alternate_class, ' subject">
						<div ', (!empty($topic['quick_mod']['modify']) ? 'id="topic_' . $topic['first_post']['id'] . '"  ondblclick="oQuickModifyTopic.modify_topic(\'' . $topic['id'] . '\', \'' . $topic['first_post']['id'] . '\');"' : ''), '>';

			// Now we handle the icons
			echo '
							<div class="icons">';
			if ($topic['is_locked'])
				echo '
								<span class="generic_icons lock floatright"></span>';
			if ($topic['is_sticky'])
				echo '
								<span class="generic_icons sticky floatright"></span>';
			if ($topic['is_redirect'])
				echo '
								<span class="generic_icons move floatright"></span>';
			if ($topic['is_poll'])
				echo '
								<span class="generic_icons poll floatright"></span>';
			echo '
							</div>';
			
			echo '
							<div class="message_index_title">
								', $topic['new'] && $context['user']['is_logged'] ? '<a href="' . $topic['new_href'] . '" id="newicon' . $topic['first_post']['id'] . '"><span class="new_posts">' . $txt['new'] . '</span></a>' : '', '
								<span class="preview', $topic['is_sticky'] ? ' bold_text' : '', '" title="', $topic[(empty($settings['message_index_preview_first']) ? 'last_post' : 'first_post')]['preview'], '">
									<span id="msg_', $topic['first_post']['id'], '">', $topic['first_post']['link'], ($context['can_approve_posts'] && !$topic['approved'] ? '&nbsp;<em>(' . $txt['awaiting_approval'] . ')</em>' : ''), '</span>
								</span>
							</div>
							<p class="floatleft">', $txt['started_by'], ' ', $topic['first_post']['member']['link'], '
								<small id="pages', $topic['first_post']['id'], '">', $topic['pages'], '</small>
							</p>
							<br class="clear" />
						</div>
					</td>
					<td class="', $color_class, ' stats">', $topic['replies'], ' ', $txt['replies'], '<br />', $topic['views'], ' ', $txt['views'], '</td>
					<td class="', $alternate_class, ' lastpost">
						', sprintf($txt['last_post_topic'], '<a href="' . $topic['last_post']['href'] . '">' . $topic['last_post']['time'] . '</a>', $topic['last_post']['member']['link']), '
					</td>';

			// Show the quick moderation options?
			if (!empty($context['can_quick_mod']))
			{
				echo '
					<td class="', $color_class, ' moderation">';
				if ($options['display_quick_mod'] == 1)
					echo '
						<input type="checkbox" name="topics[]" value="', $topic['id'], '" class="input_check" />';
				else
				{
					// Check permissions on each and show only the ones they are allowed to use.
					if ($topic['quick_mod']['remove'])
						echo '<a href="', $scripturl, '?action=quickmod;board=', $context['current_board'], '.', $context['start'], ';actions[', $topic['id'], ']=remove;', $context['session_var'], '=', $context['session_id'], '" onclick="return confirm(\'', $txt['quickmod_confirm'], '\');"><span class="generic_icons delete" title="', $txt['remove_topic'], '"></a>';

					if ($topic['quick_mod']['lock'])
						echo '<a href="', $scripturl, '?action=quickmod;board=', $context['current_board'], '.', $context['start'], ';actions[', $topic['id'], ']=lock;', $context['session_var'], '=', $context['session_id'], '" onclick="return confirm(\'', $txt['quickmod_confirm'], '\');"><span class="generic_icons lock" title="', $txt['set_lock'], '"></span></a>';

					if ($topic['quick_mod']['lock'] || $topic['quick_mod']['remove'])
						echo '<br />';

					if ($topic['quick_mod']['sticky'])
						echo '<a href="', $scripturl, '?action=quickmod;board=', $context['current_board'], '.', $context['start'], ';actions[', $topic['id'], ']=sticky;', $context['session_var'], '=', $context['session_id'], '" onclick="return confirm(\'', $txt['quickmod_confirm'], '\');"><span class="generic_icons sticky" title="', $txt['set_sticky'], '"></span></a>';

					if ($topic['quick_mod']['move'])
						echo '<a href="', $scripturl, '?action=movetopic;current_board=', $context['current_board'], ';board=', $context['current_board'], '.', $context['start'], ';topic=', $topic['id'], '.0"><span class="generic_icons move" title="', $txt['move_topic'], '"></span></a>';
				}
				echo '
					</td>';
			}
			echo '
				</tr>';
		}

		if (!empty($context['can_quick_mod']) && $options['display_quick_mod'] == 1 && !empty($context['topics']))
		{
			echo '
				<tr class="titlebg">
					<td colspan="5" class="righttext" id="quick_actions">
						<select class="qaction" name="qaction"', $context['can_move'] ? ' onchange="this.form.move_to.disabled = (this.options[this.selectedIndex].value != \'move\');"' : '', '>
							<option value="">--------</option>';

			foreach ($context['qmod_actions'] as $qmod_action)
				if ($context['can_' . $qmod_action])
					echo '
							<option value="' . $qmod_action . '">' . $txt['quick_mod_'  . $qmod_action] . '</option>';

			echo '
						</select>';

			// Show a list of boards they can move the topic to.
			if ($context['can_move'])
				echo '
			<span id="quick_mod_jump_to">&nbsp;</span>';

			echo '
						<input type="submit" value="', $txt['quick_mod_go'], '" onclick="return document.forms.quickModForm.qaction.value != \'\' &amp;&amp; confirm(\'', $txt['quickmod_confirm'], '\');" class="button_submit qaction" />
					</td>
				</tr>';
		}

		echo '
			</tbody>
		</table>
	</div>';

		// Finish off the form - again.
		if (!empty($context['can_quick_mod']) && $options['display_quick_mod'] > 0 && !empty($context['topics']))
			echo '
	<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '" />
	</form>';

		echo '
	<div class="pagesection">
		', template_button_strip($context['normal_buttons'], 'right'), '
		', !empty($modSettings['topbottomEnable']) ? $context['menu_separator'] . '<a href="#main_content_section" class="topbottom floatleft">' . $txt['go_up'] . '</a>' : '', '
		<div class="pagelinks">', $context['page_index'], '</div>
	</div>';
	}

	// Show breadcrumbs at the bottom too.
	theme_linktree();

	if (!empty($context['can_quick_mod']) && $options['display_quick_mod'] == 1 && !empty($context['topics']) && $context['can_move'])
		echo '
			<script><!-- // --><![CDATA[
				if (typeof(window.XMLHttpRequest) != "undefined")
					aJumpTo[aJumpTo.length] = new JumpTo({
						sContainerId: "quick_mod_jump_to",
						sClassName: "qaction",
						sJumpToTemplate: "%dropdown_list%",
						iCurBoardId: ', $context['current_board'], ',
						iCurBoardChildLevel: ', $context['jump_to']['child_level'], ',
						sCurBoardName: "', $context['jump_to']['board_name'], '",
						sBoardChildLevelIndicator: "==",
						sBoardPrefix: "=> ",
						sCatSeparator: "-----------------------------",
						sCatPrefix: "",
						bNoRedirect: true,
						bDisabled: true,
						sCustomName: "move_to"
					});
			// ]]></script>';

	// Javascript for inline editing.
	echo '
<script src="', $settings['default_theme_url'], '/scripts/topic.js"></script>
<script><!-- // --><![CDATA[
	var oQuickModifyTopic = new QuickModifyTopic({
		aHidePrefixes: Array("lockicon", "stickyicon", "pages", "newicon"),
		bMouseOnDiv: false,
	});
// ]]></script>';

	template_topic_legend();
}

function template_topic_legend()
{
	global $context, $settings, $txt, $modSettings;

	echo '
	<div class="tborder" id="topic_icons">
		<div class="description">
			<p class="floatright" id="message_index_jump_to">&nbsp;</p>';

	if (empty($context['no_topic_listing']))
		echo '
			<p class="floatleft">', !empty($modSettings['enableParticipation']) && $context['user']['is_logged'] ? '
				<img src="' . $settings['images_url'] . '/icons/profile_sm.png" alt="" class="centericon" /> ' . $txt['participation_caption'] . '<br />' : '', '
				'. ($modSettings['pollMode'] == '1' ? '<span class="generic_icons poll centericon"></span> ' . $txt['poll'] : '') . '<br />
				<img src="' . $settings['images_url'] . '/post/moved.png" alt="" class="centericon sizefix" /> ' . $txt['moved_topic'] . '<br />
			</p>
			<p>
				<span class="generic_icons lock centericon"></span> ' . $txt['locked_topic'] . '<br />
				<span class="generic_icons sticky centericon"></span> ' . $txt['sticky_topic'] . '<br />
			</p>';

	if (!empty($context['jump_to']))
		echo '
			<script><!-- // --><![CDATA[
				if (typeof(window.XMLHttpRequest) != "undefined")
					aJumpTo[aJumpTo.length] = new JumpTo({
						sContainerId: "message_index_jump_to",
						sJumpToTemplate: "<label class=\"smalltext\" for=\"%select_id%\">', $context['jump_to']['label'], ':<" + "/label> %dropdown_list%",
						iCurBoardId: ', $context['current_board'], ',
						iCurBoardChildLevel: ', $context['jump_to']['child_level'], ',
						sCurBoardName: "', $context['jump_to']['board_name'], '",
						sBoardChildLevelIndicator: "==",
						sBoardPrefix: "=> ",
						sCatSeparator: "-----------------------------",
						sCatPrefix: "",
						sGoButtonLabel: "', $txt['quick_mod_go'], '"
					});
			// ]]></script>';

	echo '
			<br class="clear" />
		</div>
	</div>';
}

?>