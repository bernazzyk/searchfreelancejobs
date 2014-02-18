<html>
<head>
    <title>Authentication required</title>
</head>
<body style="background: #DBDBDB url(/static/images/website/page-background.jpg) no-repeat scroll center top; font: normal 14px Arial, sans-serif;">

<div style="width: 960px; margin: 100px auto">
<img width="308" height="32" alt="" src="/media/image/new-logo.png">
<form method="POST" action="/">
    <table cellpadding="4" cellspacing="2" style="width: 300px; margin: 200px auto">
        <?php
if (isset($_auth_errors) && !empty($_auth_errors))
{
    foreach ($_auth_errors as $err)
    {
        echo '<tr><td colspan="2" style="color:#F00;">'.$err.'</td></tr>';
    }
}
?>
        <tr>
            <td>
                <label for="username">Username</label>
            </td>
            <td>
                <input type="text" value="" id="username" name="username" />
            </td>
        </tr>
        <tr>
            <td>
                <label for="password">Password</label>
            </td>
            <td>
                <input type="password" value="" id="password" name="password" />
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <input type="submit" value="Login" name="submit" />
            </td>
        </tr>
    </table>
</form>
</div>
</body>
</html>