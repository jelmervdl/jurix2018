<?php
error_reporting(E_ALL);
ini_set('display_errors', true);

session_start();

function row_id($i, $row) {
	$row_hash = sha1(implode("|", $row));
	return sprintf('%d_%s', $i, $row_hash);
}

function read_signups()
{
	$rows = array();
	
	$fh = fopen('../data/signups-jurix.txt', 'r');
	
	while (($row = fgetcsv($fh)) !== false)
		$rows[] = $row;

	fclose($fh);
	
	$header = array_shift($rows);

	return array($header, $rows);
}

function delete_signups($ids)
{
	$fh = fopen('../data/signups-jurix.txt', 'a+');

	flock($fh, LOCK_EX);

	fseek($fh, 0, SEEK_SET);

	$i = -1; // Skip the header when counting
	$rows = array();

	while (($row = fgetcsv($fh)) !== false) {
		if ($i === -1 || !in_array(row_id($i, $row), $ids))
			$rows[] = $row;
		$i++;
	}

	ftruncate($fh, 0);
	
	fseek($fh, 0, SEEK_SET);

	foreach ($rows as $row) 
		fputcsv($fh, $row);

	flock($fh, LOCK_UN);
	fclose($fh);
}

function view_login()
{
	if (!isset($_POST['password']))
		return html_page(html_login_form());

	sleep(1);

	$auth_hashes = require '../data/auth.php';

	$hash = sha1($_POST['password']);

	if (!in_array($hash, $auth_hashes))
		throw new Exception('Invalid password');
	
	$_SESSION['authenticated'] = time();

	header('Location: signups.php');
	return 'You are authenticated, redirecting again';
}

function view_logout()
{
	if ($_SERVER['REQUEST_METHOD'] == 'POST')
		session_destroy();

	header('Location: signups.php');
	return 'You are logged out, redirecting again';
}

function html_page($body)
{
	header('Content-type: text/html; charset=utf-8');
	return <<<HTML
<html>
	<head>
		<meta charset="utf-8">
		<title>Sign ups &middot; JURIX 2018</title>
		<style>
			body {
				font-family: sans-serif;
			}

			table {
				width: 100%;
			}
		</style>
	</head>
	<body>
		$body
	</body>
</html>
HTML;
}

function view_index()
{
	return html_page('<a href="signups.php?view=download">Download as csv</a>'
		. '<form method="post" action="signups.php?view=logout">'
		. '<button type="submit">Logout</button>'
		. '</form>'
		. '<form method="post" action="signups.php?view=update">'
		. html_signup_table()
		. '<button type="submit" name="action" value="delete">Remove from list</button>'
		. '</form>');
}

function view_update()
{
	if (isset($_POST['action']) && $_POST['action'] == 'delete')
		delete_signups($_POST['row']);
	
	header('Location: signups.php?view=index');
	return 'Returning to <a href="signups.php?view=index">index</a>';
}

function view_download()
{
	header('Content-Type: application/csv');
	header('Content-Disposition: attachment; filename=signups.csv');
	header('Pragma: no-cache');
	readfile('../data/signups-jurix.txt');
	return '';
}

function html_login_form()
{
	return '<form method="post" action="signups.php?view=login">'
		.'<label for="password-field">Password:</label>'
		.'<input id="password-field" type="password" name="password" autofocus>'
		.'<button type="submit">Login</button>'
		.'</form>';
}

function html_signup_table()
{
	list($header, $rows) = read_signups();
	
	$ths = array('<th></th>');

	foreach ($header as $cell)
		$ths[] = sprintf('<th>%s</th>', htmlentities($cell, ENT_COMPAT, 'UTF-8'));

	$trs = array('<tr>' . implode('', $ths) . '</tr>');

	foreach ($rows as $i => $row) {
		$tds = array(sprintf('<th><input type="checkbox" name="row[]" value="%s"></th>', row_id($i, $row)));

		foreach ($row as $cell)
			$tds[] = sprintf('<td>%s</td>', htmlentities($cell, ENT_COMPAT, 'UTF-8'));

		$trs[] = sprintf('<tr>%s</tr>', implode('', $tds));
	}

	return '<table>' . implode("\n", $trs) . '</table>';
}

$view = isset($_GET['view']) ? $_GET['view'] : 'index';

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] < time() - 2 * 3600)
	$view = 'login';
else
	$_SESSION['authenticated'] = time();

echo call_user_func('view_' . $view);



