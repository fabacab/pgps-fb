<?php
require_once 'lib/facebook/src/facebook.php';
require_once 'lib/pgps-fb.php';
function he ($str) {
    return htmlentities($str, ENT_QUOTES, 'UTF-8');
}

function getFacebookAppToken () {
    $url = 'https://graph.facebook.com/oauth/access_token?'.
           'client_id=' . getenv('FACEBOOK_APP_ID') .
           '&client_secret=' . getenv('FACEBOOK_SECRET') .
           '&grant_type=client_credentials';
    $res = file_get_contents($url);
    list(, $token) = explode('=', $res);
    return $token;
}
function getFlashMessage ($output = 'html', $before = '<li>', $after = '</li>') {
    global $pgps_flashmsg;
    if (count($pgps_flashmsg)) {
        $out = '';
        foreach ($pgps_flashmsg as $msg) {
            switch ($output) {
                case 'HTML':
                case 'html':
                    $out .= '<ul>';
                    $out .= $before . he($msg) . $after;
                    $out .= '</ul>';
                default:
                    break;
            }
        }
        return $out;
    }
}

// Initialize.
$FB = new Facebook(array(
    'appId' => getenv('FACEBOOK_APP_ID'),
    'secret' => getenv('FACEBOOK_SECRET'),
    'sharedSession' => true,
    'trustForwarded' => true,
));
$user_id = $FB->getUser();
$pgps_errors = array();
$pgps_flashmsg = array();

if ($user_id) {
    try {
        // Get basic data from the Graph API.
        $me = $FB->api('/me');
        $res = $FB->api('/me?fields=picture');
        $my_picture_url = $res['picture']['data']['url'];
        // Get a list of the user's friends.
        $friends = $FB->api('/me/friends?fields=id,name,link,installed');
    } catch (FacebookApiException $e) {
        if (!$FB->getAccessToken()) {
            $url = ($_SERVER['HTTPS']) ? 'https:// ': 'http://';
            header('Location: ' . $url . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            exit();
        }
    }

    $my_name = $me['name'];
    $my_link = $me['link'];
    $person = new PersonWithPronouns($user_id);

    if ($_REQUEST['submit']) {
        $old_person = clone $person;
        $person->gender = $_REQUEST['gender'];
        $person->personal_subjective = $_REQUEST['personal_subjective'];
        $person->personal_objective = $_REQUEST['personal_objective'];
        $person->possesive = $_REQUEST['possesive'];
        $person->reflexive = $_REQUEST['reflexive'];
        // Only save new data if the logged-in Facebook user is updating themself.
        if ($_REQUEST['facebook_id'] === $user_id) {
            if ($person->persist()) {
                array_push($pgps_flashmsg, 'Saved your new gender and pronoun information.');
            }
        }
        // Determine if any of the gender or pronoun fields have changed.
        if ($old_person != $person) {
            // If they have, send a notifcation via Facebook Notifications API to users
            // of this app. For users not using this app, send a Facebook message.
            // Get an App token.
            $FB->setAccessToken(getFacebookAppToken());
            $num_notifications = 0;
            foreach ($friends['data'] as $friend) {
                if ($friend['installed']) {
                    // Send a notification to this friend.
                    $their = ($person->possesive) ? $person->possesive: 'their';
                    try {
                        $FB->api("/{$friend['id']}/notifications", 'post', array(
                            'template' => "@[{$me['id']}] changed $their gender pronouns.",
                            'href' => "?show_user={$me['id']}"
                        ));
                        $num_notifications++;
                    } catch (FacebookApiException $e) {
                        $pgps_errors[] = $e;
                    }
                }
            }
            if ($num_notifications) {
                array_push($pgps_flashmsg, "Your gender pronouns have been updated and a notification was sent to $num_notifications of your friends.");
            }
            if ($pgps_errors) {
                array_push($pgps_flashmsg, "Some errors occurred.");
                foreach ($pgps_errors as $err) {
                    $msg = "An error of type {$err->getType()} occurred: " . json_encode($err->getResult());
                    array_push($pgps_flashmsg, $msg);
                }
            }
        }
    }

    if (!empty($_GET['show_user']) && $friends['data']) {
        foreach ($friends['data'] as $friend) {
            if ($_GET['show_user'] == $friend['id']) {
                $my_name = $friend['name'];
                $my_link = $friend['link'];
                $my_picture_url = "https://graph.facebook.com/{$friend['id']}/picture?type=square";
                $person = new PersonWithPronouns($friend['id']);
            }
        }
    }

    // Set Gender from Facebook's preference, if it exists.
    if ($me['gender'] && !$person->gender) {
        $person->gender = $me['gender'];
    }

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
    <div class="FlashMessage"><?php print getFlashMessage();?></div>
    <p>Hi, my name is <a href="<?php print he($my_link);?>" target="_top"><img alt="" src="<?php print he($my_picture_url);?>" /><?php print he($my_name);?></a>. (<a id="fb-logout-button" class="FacebookButton" href="<?php print $_SERVER['PHP_SELF'];?>">Log out of Facebook<?php if (!empty($_GET['show_user'])) : print he(" ({$me['name']})"); endif;?></a><?php if (!empty($_GET['show_user'])) :?>. <a href="<?php print $_SERVER['PHP_SELF'];?>">Edit my own gender pronouns.</a><?php endif;?>)</p>
    <form id="pgps-fb-form" action="<?php print $_SERVER['PHP_SELF']?>">
        <input type="hidden" name="facebook_id" value="<?php $val = (empty($_GET['show_user'])) ? $user_id: $_GET['show_user']; print he($val);?>" />
        <fieldset><legend>My gender and preferred pronouns&hellip;</legend>
            <p><label>My gender is <input id="gender" name="gender" placeholder="androsnuffleupagus and supercalifragilisticexpialidocious" value="<?php print he($person->gender);?>"<?php if (!empty($_GET['show_user'])) : print ' readonly="readonly" '; endif;?>/></label>, and when you refer to me please take your cues from the following examples:</p>
            <ul>
                <li><label for="pgp-personal-subjective">Personal subjective pronoun:</label> "We hung out last week and <input id="pgp-personal-subjective" name="personal_subjective" placeholder="they/zie/she/he" value="<?php print he($person->personal_subjective);?>" <?php if (!empty($_GET['show_user'])) : print ' readonly="readonly" '; endif;?>/> looked great!"</li>
                <li><label for="pgp-personal-objective">Personal objective pronoun:</label> "When I heard <input id="pgp-personal-objective" name="personal_objective" placeholder="them/zim/her/him" value="<?php print he($person->personal_objective);?>" <?php if (!empty($_GET['show_user'])) : print ' readonly="readonly" '; endif;?>/> use the correct pronoun, I was <em>so pleased</em>!"</li>
                <li><label for="pgp-possesive">Possesive pronoun:</label> "I think <input id="pgp-possesive" name="possesive" placeholder="their/zir/her/his" value="<?php print he($person->possesive);?>" <?php if (!empty($_GET['show_user'])) : print ' readonly="readonly" '; endif;?>/> points are important to consider."</li>
                <li><label for="pgp-reflexive">Reflexive pronoun:</label> "<?php print he($my_name);?> made it <input id="pgp-reflexive" name="reflexive" placeholder="themself/zimself/herself/himself" value="<?php print he($person->reflexive);?>" <?php if (!empty($_GET['show_user'])) : print ' readonly="readonly" '; endif;?>/>, how awesome is that!"</li>
            </ul>
        </fieldset>
<!--
        <fieldset><legend>App Preferences</legend>
            <?php // TODO! ?>
        </fieldset>
-->
        <input type="submit" name="submit" value="I see no reason why the gunpowder treason should ever be forgot." />
    </form>
    <p>(<a href="http://www.grammar-monster.com/lessons/pronouns_different_types.htm" target="_blank">Grammar is fun</a>!)</p>
<?php endif; ?>
</div>
</body>
</html>
