# tumblr-chat-messages-downloader

A script to download tumblr chat messages
```
Usage: ./tumblr.php username password blog [conversation] [file]
```
First, run the script just with your Tumblr username and password, and the URL for the specific blog **without** the ".tumblr.com" portion. (The blog must be associated with your Tumblr account.)

You will get a list of all available conversations, which will appear as follows:

```
111111111 username <=> chatuser1
222222222 username <=> chatuser2
333333333 username <=> chatuser3
...
```

Once you have acquired this list, run the script again, this time using the number of the conversation you want to download as the fourth argument: 
```
Usage: ./tumblr.php username password blog 111111111
```
Messages will be displayed in the terminal. 

If you want the messages to be saved to a file, use a file name for the fifth argument, as shown below:
```
Usage: ./tumblr.php username password blog 111111111 file.txt
```
WARNING: If the filename you have chosen already contains content, its original contents will be *overwritten without warning*.

This script has not yet been tested very well, so it may have bugs, ot not work at all. ~~For instance, it handles cases where the blog password is incorrect very badly, as there are no relevant checks.~~
