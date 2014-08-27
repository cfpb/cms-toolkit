## FAQ

1. Why do I have to make a child plugin for this to do anything?

The short answer is we wanted to build this plugin in as robust a way as
possible to give you the tools to do what you want with it. There are other
plugins that give you a GUI for post type and taxonomy registration but this 
plugin takes a different approach to abstracting those repetitivie processes 
away. Want to build an options screen on top of this plugin to do post type 
registration? Go for it! However you end up using it we hope you have fun.

2. Why all the namespacing?

We wanted to make the undelying code as readable as possible and thought there
might be a world where a WordPress install has two classes called `MetaBox`, for
example. Since the tools here are all generic methods, the namespaces add a
little more insurance from naming collisions. This way when you're writing a
child plugin you know you're always getting `CFPB`'s `MetaBox` and not some
other vendor's.

3. Why a plugin and not a composer library?

Honestly, it could probably be both. We released it as a plugin because the
project started as a plugin first and it was just easier to continue developing
it that way.

4. I'm getting this error: `The cms-toolkit plugin requires PHP version 5.3 or
higher...`, what's up with that?

This plugin requires several features found only in PHP version 5.3 or higher.
If you really want to use this plugin either upgrade your PHP to at least 5.3.0
or contact your system administrator and request it be updated.