<?php
require_once 'lib/facebook/src/facebook.php';
require_once 'lib/pgps-fb.php';
function he ($str) {
    return htmlentities($str, ENT_QUOTES, 'UTF-8');
}

$FB = new Facebook(array(
    'appId' => getenv('FACEBOOK_APP_ID'),
    'secret' => getenv('FACEBOOK_SECRET'),
    'sharedSession' => true,
    'trustForwarded' => true,
));
$user_id = $FB->getUser();

if ($user_id) {
    try {
        // Get basic data from the Graph API.
        $me = $FB->api('/me');
    } catch (FacebookApiException $e) {
        if (!$FB->getAccessToken()) {
            $url = ($_SERVER['HTTPS']) ? 'https:// ': 'http://';
            header('Location: ' . $url . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            exit();
        }
    }
}

$person = new PersonWithPronouns($user_id);
if ($_REQUEST['submit']) {
    $person->gender = $_REQUEST['gender'];
    $person->personal_subjective = $_REQUEST['personal_subjective'];
    $person->personal_objective = $_REQUEST['personal_objective'];
    $person->possesive = $_REQUEST['possesive'];
    $person->reflexive = $_REQUEST['reflexive'];
    $person->persist();
}
?><!DOCTYPE html>
<html lang="en">
<head>
<title>Preferred Gender Pronouns for Facebook</title>
</head>
<body>
<div id="fb-root"></div>
<script>
window.fbAsyncInit = function () {
    // init the FB JS SDK
    FB.init({
        appId: '<?php print he(getenv('FACEBOOK_APP_ID'), ENT_QUOTES, 'UTF-8');?>',
        status: true,
        cookie: true,
        xfbml: true
    });

    FB.Event.subscribe('auth.login', function (response) {
        // Reload, but by navigating, in case we're in a Canvas.
        // Reloading lets the FB PHP SDK read the cookie set by the JS SDK.
        window.location = window.location;
    });

    var el = document.getElementById('fb-logout-button');
    if (el) {
        el.addEventListener('click', function() {
            FB.logout();
        });
    }
};

(function(d, s, id){
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) {return;}
    js = d.createElement(s); js.id = id;
    js.src = '//connect.facebook.net/en_US/all.js';
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
</script>
<div id="MainContent">
    <h1>Preferred Gender Pronouns for Facebook</h1>
<?php if (!$user_id) : ?>
    <section id="explanation">
        <p>This app lets you break out of the binary gender restriction on Facebook. Type your gender and the pronouns you use in free-form text fields. Come back to change either at any time you like, as often as you please. You can even let this app send your friends notifcations any time you make a change to your preferred gender pronoun(s), to help avoid "that awkward moment when you're talking about a friend by their new pronouns, but your other friends haven't realized it yet."</p>
</section>
    <p><span class="fb-login-button">Log in to start using Preferred Gender Pronouns for Facebook</span></p>
<?php else : ?>
    <p>Hi, my name is <?php print $me['name'];?>. (<a id="fb-logout-button" class="FacebookButton" href="">Log out</a>)</p>
    <form id="pgps-fb-form" action="<?php print $_SERVER['PHP_SELF']?>">
        <fieldset><legend>My gender and preferred pronouns&hellip;</legend>
            <p>My gender is <input id="gender" name="gender" placeholder="androsnuffleupagus and supercalifragilisticexpialidocious" value="<?php print he($person->gender);?>" />, and my pronouns are:</p>
            <ul>
                <li><input id="pgp-personal-subjective" name="personal_subjective" placeholder="they/zie" value="<?php print he($person->personal_subjective);?>" /> <span class="helptext">personal subjective pronoun</span></li>
                <li><input id="pgp-personal-objective" name="personal_objective" placeholder="them/zim" value="<?php print he($person->personal_objective);?>" /> <span class="helptext">personal objective pronoun</span></li>
                <li><input id="pgp-possesive" name="possesive" placeholder="theirs/zirs" value="<?php print he($person->possesive);?>" /> <span class="helptext">possesive pronoun</span></li>
                <li><input id="pgp-reflexive" name="reflexive" placeholder="themself/zimself" value="<?php print he($person->reflexive);?>" /> <span class="helptext">reflexive pronoun</span></li>
            </ul>
        </fieldset>
        <fieldset><legend>App Preferences</legend>
            <?php // TODO! ?>
        </fieldset>
        <input type="submit" name="submit" value="I see no reason why the gunpower treason should ever be forgot." />
    </form>
    <p>(<a href="http://www.grammar-monster.com/lessons/pronouns_different_types.htm">Grammar is fun</a>!)</p>
<?php endif; ?>
</div>
</body>
</html>
