# habits

A simple, web-based habit tracker.

**This is just my own personal software.** I use it a lot and am very satisifed with it, but there are no guarantees that this will work for you. It is suited to my own needs and idiosyncracies.

* The habit\_pause and habit\_weekday tables are unused. The related features turned out to be too complex and not very useful.
* The touch\_counter table is unused.

I did not bother to make it mobile-ready, since I only ever use it on desktop.

It SHOULD work under any webserver that has php and mysql enabled, apache or nginx or a similar server.

# Architecture

There is precious little "architecture". 

* Publically accessible files reside in htdocs/.
* The root document is htdocs/index.php.
* CSS and JS reside in htdocs/css and htdocs/js, respectively.
* The code library is in php/, along with the templating and the config file.
* db migrations are under migrations/.

# Setup

To set it up:

1. Copy the source tree to a web-accessable location.
2. Inside the project root, run: "cp php/config.php.dist php/config.php"
3. Edit php/config.php to set the db config.
4. Set up the db.
5. Inside the project root, run mysql client on the db, and inside mysql, run: "source migrations/setup.sql"

Point your browser to htdocs/index.php.

Please do not be alarmed about the lack of any git history. I used my real name on the local repo, and I don't want to expose that to the world.

# License

The license for this software is CC-BY-SA (https://creativecommons.org/licenses/by-sa/2.0/). Feel free to share and modify, just name Demalteb (https://reddit.com/user/demalteb) as the original author, and share the source.
