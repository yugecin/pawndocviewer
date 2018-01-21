<?php

function pawndoc2html($input)
{
	ob_start();

	$x = new SimpleXMLElement($input);

	//$content = print_r($x, true);

	$table = array(
		'T' => array(),
		'C' => array(),
		'F' => array(),
		'M' => array(),
	);

	$types = array(
		'T' => 'enumeration',
		'C' => 'constant',
		'F' => 'variable',
		'M' => 'function',
	);

	$header = "<h1>{$x->assembly->name}</h1>";

	if (isset($x->general->summary))
	{
		$summary = trim($x->general->summary);
		$header .= "<dl><dd>{$summary}</dd></dl>";
	}
	else if (isset($x->general))
	{
		$header .= '<dl><dd>' . trim($x->general) . '</dd></dl>';
	}

	foreach ($x->members->member as $m)
	{
		//echo("<!--" . print_r($m, true) . "-->");
		$attribs = $m->attributes();

		$type = substr($attribs['name'], 0, 1);
		$name = substr($attribs['name'], 2);

		pawndoc2html_print_member($types, $m, $type, $name, $attribs);
		$table[$type][] = $name;
	}

	$content = ob_get_contents();
	ob_end_clean();
	ob_start();

	foreach ($types as $key => $type) {

		$title = strtoupper(substr($type, 0, 1)) . substr($type, 1);

		$amount = count($table[$key]);
		echo '<article>';
		echo "<h2 class=\"{$title}s\">{$title}s ({$amount})</h2>";
		echo '<ul>';
		foreach ($table[$key] as $elem)
		{
			echo "<li><a href=\"#{$elem}()\">{$elem}</a></li>";
		}
		echo '</ul></article>';
	}

	$contenttable = ob_get_contents();
	ob_end_clean();

	return <<<OUTPUT
{$header}
{$contenttable}
{$content}
<p>
See the PAWN language guide page 52:
 <em>A tutorial introduction - Documentation comments</em>
<span><a href="https://github.com/yugecin/pawndocviewer" target="_blank">
yugecin/pawndocviewer
</a></span>
</p>
OUTPUT;
}

function pawndoc2html_print_member(
	$types,
	$elem,
	$type,
	$name,
	$attribs,
	$top = true)
{
	$typename = $types[$type];

	echo "<section id=\"{$name}()\" class=\"{$typename}\">" .
		"<h2><a href=\"#{$name}()\">{$name}</a><a class=\"wiki\"" .
		" href=\"http://wiki.sa-mp.com/wiki/{$name}\"" .
		" target=_blank>wiki</a><span>{$typename}</span></h2>";

	// summary
	if (isset($elem->summary))
	{
		echo "<dl><dd>{$elem->summary}</dd></dl>";
	}

	// enumeration
	if ($type == 'T')
	{
		echo '<dl><dt>Members</dt><dd><br/>';

		foreach ($elem->member as $_m)
		{
			$_attribs = $_m->attributes();

			$_type = substr($_attribs['name'], 0, 1);
			$_name = substr($_attribs['name'], 2);

			pawndoc2html_print_member(
				$types,
				$_m,
				$_type,
				$_name,
				$_attribs,
				false
			);
		}

		echo '</dd></dl>';
	}

	// syntax
	if (isset($attribs['syntax']))
	{
		echo '<dl class=\"syntax\"><dt>Syntax</dt><dd><strong>' .
			$attribs['syntax'] . '</strong></dd></dl>';
	}

	// param (syntax params)
	if (!isset($elem->param))
	{
		goto noparams;
	}

	echo '<dl><dd><table>';

	$dotdotdot = null;

	foreach ($elem->param as $par)
	{
		$att = $par->attributes();

		if ($att['name'] == '...')
		{
			$dotdotdot = array('info' => '', 'desc' => '');
			if (!empty($par->paraminfo))
			{
				$dotdotdot['info'] = $par->paraminfo;
			}
			if (!empty($par[0]))
			{
				$dotdotdot['desc'] = trim($par[0]);
			}
			continue;
		}

		$parinfo = '';
		if (!empty($par->paraminfo))
		{
			$parinfo = '<span class="paraminfo">&lt;' .
				$par->paraminfo . '&gt;</span>';
		}
		$pardesc = '';
		if (!empty($par[0]))
		{
			$pardesc = trim($par[0]);
		}

		echo "<tr><td>{$att["name"]}</td><td>{$parinfo}</td><td>" .
			$pardesc . '</td></tr>';
	}

	if ($dotdotdot != null)
	{
		echo '<tr><td>...</td><td><span class="paraminfo">&lt;' .
			$dotdotdot['info'] . '&gt;</span></td><td>' .
			$dotdotdot['desc'] . '</td></tr>';
	}

	echo '</table></dd></dl>';

noparams:

	// not for enumerations
	if( $type != 'T')
	{
		// value
		if( isset($attribs['value']))
		{
			echo '<dl><dt>Value</dt><dd>' . $attribs['value'] .
				'</dd></dl>';
		}

		// tag names
		if( isset($elem->tagname))
		{
			$tag = $elem->tagname->attributes();
			echo '<dl><dt>Tag</dt><dd>' . $tag['value'] .
				'</dd></dl>';
		}
	}

	// returns
	if (isset($elem->returns))
	{
		echo "<dl><dt>Returns</dt><dd>{$elem->returns}</dd></dl>";
	}

	// remarks
	if (isset($elem->remarks))
	{
		echo '<dl><dt>Remarks</dt><dd>';
		foreach ($elem->remarks as $remark)
		{
			echo "<p>{$remark}</p>";
		}
		echo '</dd></dl>';
	}

	// referrers (used by)
	if (isset($elem->referrer))
	{
		echo '<dl><dt>Used by</dt><dd>';

		foreach ($elem->referrer as $ref)
		{
			$att = $ref->attributes();
			echo '<a href="#' . $att['name'] . '()">' .
				$att['name'] . '()</a> &nbsp; ';
		}

		echo '</dd></dl>';
	}

	// dependency (depends on)
	if (isset($elem->dependency))
	{
		echo '<dl><dt>Depends on</dt><dd>';

		foreach ($elem->dependency as $ref)
		{
			$att = $ref->attributes();
			echo '<a href="#' . $att['name'] . '()">' .
				$att['name'] . '()</a> &nbsp; ';
		}

		echo '</dd></dl>';
	}

	// attributes
	if (isset($elem->attribute))
	{
		$attr = $elem->attribute->attributes();
		echo "<dl><dt>Attributes</dt><dd>{$attr['name']}</dd></dl>";
	}

	// stacksize
	if (isset($elem->stacksize))
	{
		$tag = $elem->stacksize->attributes();
		echo '<dl><dt>Estimated stack usage</dt><dd>' .
			$tag['value'] . ' cell';
		if ($tag['value'] != 1) {
			echo ('s');
		}
		echo '</dd></dl>';
	}

	if ($top)
	{
		echo '<p><a href="#top">Top</a></p>';
	}

	echo '</section>';
}

