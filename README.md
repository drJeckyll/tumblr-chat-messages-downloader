# tumblr-chat-messages-downloader
Script to download tumblr chat messages
```
Usage: ./tumblr.php username password blog [conversation] [file]
```
First run script just with username, password and blog name **without .tumblr.com** in it.

You will get list of all available conversations

Conversations: 
```
111111111 username <=> chatuser1
222222222 username <=> chatuser2
333333333 username <=> chatuser3
...
```

Then run script again with conversation you want to download: 
```
Usage: ./tumblr.php username password blog 111111111
```
Messages will be dumped on screen. 

If you want them saved to a file just specify file nane in next argument:
```
Usage: ./tumblr.php username password blog 111111111 file.txt
```
WARNING: file will be overwriten without warning.

Script is not tested very well so it may have bugs, ot not work at all. ~~Theres is no check is password is correct so it will fail misserable in that is the case.~~
