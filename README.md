# Preferred Gender Pronouns for Facebook

Teach Facebook to use pronouns like "zie," "per," and more! Self-identify your gender and preferred pronouns in free-form text fields. Change them as often as you like. You can even send your friends a Facebook notification when your pronouns change.

No boxes. No drop-downs. No binaries.

*Preferred Gender Pronouns for Facebook* is a simple implementation of "[gender as a text field](http://maybemaimed.com/2011/06/30/ssexbbox-gender-is-a-text-field/)" rather than a binary drop-down, intended to replace the default Facebook profile field called "gender." (Because, for fuck's sake, Facebook, gender is not a binary, and you should know better.) This mimics the way [Diaspora](https://joindiaspora.com/) handles gender identity, which is far more respectful of, well, everyone.

You are no longer limited to using "he," "she," or "they." With Preferred Gender Pronouns for Facebook, you can use *any pronoun you like* and Facebook will use it when it refers to you.

## How it works

There are two parts to this software. The first is a Facebook app, which allows you to self-identify your gender however you like by typing your gender and each of your pronouns in open text fields. Your gender and pronouns are then saved along with your Facebook ID, much like how the phone book lists your phone number next to your name. The second is a browser plug-in, called a userscript, that automatically looks up the preferred gender pronouns of everyone you see on your Facebook wall and replaces Facebook's default pronouns with whatever pronouns that person entered in the Facebook app.

### Step 1: Entering a new gender pronoun

1. Go to [https://apps.facebook.com/gender-pronouns](https://apps.facebook.com/gender-pronouns/)
1. Log in to the app using your Facebook account, and authorize the app to access your public profile and friends list.
1. Enter your preferred gender pronouns in the text fields of the form, and save.

### Step 2 (optional): Install the Preferred Gender Pronouns for Facebook viewer

Optionally, you can download and install the Preferred Gender Pronouns for Facebook viewer in order to make Facebook display stories your friends publish using the pronouns they've entered when they completed step 1. In other words, if your friend Tyler uses "zir" and similar pronouns, when Tyler updates zir Facebook cover photo, instead of saying "Tyler updated his/her cover photo," the story will show up in your newsfeed as "Tyler updated zir cover photo."

1. If you haven't yet done so, install a Userscript manager.
    1. If you use [Mozilla Firefox](http://getfirefox.com/), install [Greasemonkey](https://addons.mozilla.org/en-US/firefox/addon/greasemonkey/).
    1. If you use [Google Chrome](https://chrome.google.com/), install [Tampermonkey](https://chrome.google.com/webstore/detail/tampermonkey/dhdgffkkebhmkfjojejmpbldmpobfkfo).
1. [Download the PGPs-FB viewer by clicking here](http://userscripts.org/scripts/source/177700.user.js).
1. Go back to [Facebook.com](https://www.facebook.com/). :)

## Credits

If you enjoy this script, please consider tossing a few metaphorical coins in [my cyberbusking hat](http://maybemaimed.com/cyberbusking/). :) Your donations are sincerely appreciated! Can't afford to part with any coin? It's cool. Tweet your appreciation or post about this app on Facebook, instead.

## Change log

Note that only the PGPs-FB client (viewer) is versioned.

* Version 0.1.1:
    * Fixed [bug](https://github.com/meitar/pgps-fb/issues/18) where pronouns containing a default pronoun were replaced multiple times.
* Version 0.1:
    * First client release.

## Known issues

* Gender pronouns are only replaced on Facebook stream messages (homepage and ticker), not on user timelines.
