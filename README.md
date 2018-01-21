
## pawndocviewer

puts PAWN compiler report in a HTML format and adds an index of all functions

use the `-rfilename` PAWN compiler option to generate a report of your code in XML format

see the PAWN language guide page 52: _A tutorial introduction - Documentation comments_ for the PAWN documentation syntax

### usage

requires [SimpleXML](https://secure.php.net/manual/en/book.simplexml.php) (enabled by default for PHP 5.1.2 and up, use `--enable-simplexml` when compiling older versions)

```
<!DOCTYPE html>
<html>
<head>
	<title>PawnoDOC</title>
	<meta name="viewport"
	content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
	<meta charset="utf-8" />
	<link rel="stylesheet" type="text/css" href="pawndoc2html.css" />
</head>
<body>
	<div>
		<?php
			require('pawndoc2html.php');
			echo pawndoc2html(file_get_contents('output.xml'));
		?>
	</div>
</body>
</html>
```

see the `pawndoc2html.css` file for default style

---

you might also be interested in [documented-samp-pawn-api](https://github.com/basdon/documented-samp-pawn-api)
