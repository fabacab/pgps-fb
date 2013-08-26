# Preferred Gender Pronouns for Facebook

This tool is a simple implementation of "[gender as a text field](http://maybemaimed.com/2011/06/30/ssexbbox-gender-is-a-text-field/)" rather than a binary drop-down, intended to replace the default Facebook profile field called "sex." (Because, for fuck's sake, Facebook, gender is not the same as sex, and you should know better.) This mimics the way [Diaspora](https://joindiaspora.com/) handles gender, which is far more respectful of, well, everyone.

Nota bene: The impetus for this project was simply to give me an easy first project in which to learn some of the Facebook APIs. As a result, this project is not intended to be a suggested or secure means of storing, sharing, or choosing gender pronouns. Feel free to use it as such, if you like, and certainly feel free to improve upon it. But my purpose here was not "build the most awesome gender pronoun sharing app evar!" Rather, it was "learn how to use the Facebook APIs." There's a difference.

Now, that said, this app allows you to self-identify your gender to your friends however you like. When you change your preferred gender pronouns, it also sends a Facebook notification to your friends who have installed this app to let them know of your new pronoun preference. This helps avoid that awkward moment when references to a person are confusing due to a gender change.

## Known issues

* Totally lacks any sense of visual and aesthetics design. (Contributions welcome.)
* Filesystem-based storage won't scale all that well.

## Some future feature ideas

* Write a Facebook client (browser plug-in?) that replaces all of Facebook's references to a gender pronoun based on its stupid male/female binary with whatever grammatically appropriate pronoun a user chose to use.
