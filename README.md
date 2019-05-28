# THIS CODE IS OBSOLETE AND WILL NOT BE UPDATED ANYMORE
Please use new repo here: https://git.jeckyll.net/published/personal/tumblr-chat-messages-downloader



# tumblr-chat-messages-downloader

A script to download tumblr chat messages
```
Usage: ./tumblr.php -u username -p password -b blog [-c conversation] [-f filename] [-s] [-d YYYYMMDD]
```
First, run the script just with your Tumblr username, password and the URL for the specific blog **without** the ".tumblr.com" portion. (The blog must be associated with your Tumblr account.)

You will get a list of all available conversations, which will appear as follows:

```
111111111 username <=> chatuser1
222222222 username <=> chatuser2
333333333 username <=> chatuser3
...
```

Once you have acquired this list, run the script again, this time using the number of the conversation you want to download: 
```
Usage: ./tumblr.php -u username -p password -b blog -c 111111111
```
Messages will be displayed in the terminal. 

If you want the messages to be saved to a file, use a file name, as shown below:
```
Usage: ./tumblr.php -u username -p password -b blog 111111111 -f file.txt
```
If **-s** option is supplied log file will be splited in multiple files (one for each day) and date will be added to file name (file-YYMMDD.txt).

If **-d** option is supplied then only chat messages from specified date will be downloaded (YYYYMMDD format).

WARNING: If the filename you have chosen already contains content, its original contents will be *overwritten without warning*.

This script has not yet been tested very well, so it may have bugs, or not work at all. ~~For instance, it handles cases where the blog password is incorrect very badly, as there are no relevant checks.~~

Full help message (displayed if no or invlid options are supplied):
````
Usage: ./tumblr.php -u username -p password -b blog [-c conversation] [-f filename] [-s] [-d YYYYMMDD]

	First run script only with username, password and blog to get list of conversations.
	Then run script again specifying conversation you want to download.

	-u, --username (required)
		tumblr username or E-mail

	-p, --password (required)
		tumblr password

	-b, --blog (required)
		tumblr blog without .tumblr.com (required)

	-c, --conversation (optional|required)
		conversation id from the list

	-d, --date YYYYMMDD (optional)
		output only log for specified date

	-f, --file filename (optional)
		output file name

	-s, --split (optional) (require -f)
		put output in separete files for each day: filename-YYYYMMDD.ext
````
