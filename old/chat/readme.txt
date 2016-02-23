BlueChat Copyright © 1999,2001 by Robert Fogt
http://www.bluesparks.com/
webmaster@bluesparks.com

BlueChat is Free software. Use it at your own risk.

You may use BlueChat in its unmodified form free of charge.
If you modify BlueChat, please place a link to http://www.bluesparks.com/
somewhere on your site.

If you link to BlueSparks, feel free to replace the logo in the upper left
corner of the chat with whatever you wish. Simply create a html file named
logo.htm and place it in the same directory as the cgi files. BlueChat will
display logo.htm instead of the default BlueSparks ad.

I also have a reciprocal links page. If you link to BlueSparks, feel free
to add your link to my site at http://www.bluesparks.com/

-=>Basic Setup<=-

1. Create a directory on your server called bluechat
    In the bluechat directory, create another directory called chat.
    (the chat directory must be named chat for the default setup)

2. Edit bc_chat.cgi and change the $imagedir variable to the complete url
    of the gif images (the bluechat directory)
    example: http://www.yourdomain.com/bluechat

Optional: (You may Edit bluechat.cgi if you wish. There are many variables
           that will change the look of BlueChat to match your site.)

3. Upload all the files to the bluechat directory.
(the cgi files must be uploaded in ascii mode, the gif files in binary mode)

4. Chmod the bluechat directory 755
    Chmod the chat directory 777
    Chmod all the cgi files 755

5. Place a link on your site to the bluechat.cgi file. example:
<a href="http://www.yourdomain.com/bluechat/bluechat.cgi">Chat</a>

-=>Thats it, you should be able to start chatting now.<=-

NOTE: If your path to perl is not /usr/bin/perl then you will need to edit
each of the cgi files and change the first line to your perl path.

NOTE: If your server requires cgi files to be located in your cgi-bin
directory, you will have to do some additional setup.
The chat directory cannot be inside the cgi-bin directory. So create the
chat directory outside the cgi-bin directory and edit bc_chat.cgi and
change the $chatpath and $chaturl variables to where the chat directory is.

NOTE: Some WinNT servers require Perl files to be named with a .pl extension.
You may need to rename all the files from .cgi to .pl
Also, you'll need to edit each file and change any .cgi to .pl since its hard
coded in a couple of places.

-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
Some advanced setup
Not necessary, but there if you need it.
-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-

----------------
- bluechat.cgi -
----------------
This file contains several variables that will change the look of the login
screen. If your site uses a certain color scheme you can change the variables
to match the rest of your site.

Another thing to mention would be the rules, the default listed are:
$rules[0] = '1. Keep all foul language and sexual content in the Adult Channel.';
$rules[1] = '2. No Flaming allowed in any channel.';

You can remove them and have no rules, or change them, add more, whatever.
They should be numbered starting at 0.

Another thing is the $additional variable. by default it is a link back to
the previous page, but could be used for anything you want. Just put the 
html inside the single quotes.

---------------
- bc_chat.cgi -
---------------
There is a variable you can set if you want to log all the chat.
Care should be taken when logging all chat since very busy chat can cause
the log to grow very big quickly. If you log the chat you should check it
and delete it every once in awhile in case it gets too big.

By default it is disabled:
$chatLogging = '0';
Change the 0 to a 1 to enable logging all the chat.

The next variable, which is used only when logging is enabled:
$logFileName = 'chat_history.htm';
Just shows the name of the file the chat is logged too. This is kept in the
chat directory, it is a html file and can be viewed with your browser.

-=-=- Filtering bad words
The @badwords variable contains words that will be filtered out of all
chat messages. The $exclude_channel variable is the channel that will not
be filtered for bad words, such as the default adult channel.

The @badwords variable can contain Perl regex strings. If you are good with
regex and can create expressions that will catch more bad words, send them too
me and I'll put them in the next release.
-=-=-=-

The other two variables are:
$ChatRefresh = 10;
which is the number of seconds between screen refreshes. If the number is
too low, it will really slow down your server. If you have tons of people
chatting, you may want to raise this number a little. This only affects
people who are not activly chatting.

$ChatMessageTime = 180;
This is the number of seconds the chat message is displayed in the chat.
After this many seconds the message disappears. If you have tons of people
chatting, you may want to lower this number to reduce the screen clutter.
If you have just a few people chatting, leave at is or raise the number.

-------------------
- bc_commands.cgi -
-------------------
Here is where you can add or remove channels.

Here is the default:
$channels[0] = 'General';
$channels[1] = 'Adult';
$channels[2] = 'Teen';

If you just want a single channel chat, delete all the lines. Or change
them, add more, or whatever.

Just make sure they are numbered starting at 0, and increment without skipping.