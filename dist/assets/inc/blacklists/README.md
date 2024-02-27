# Blacklists

## Update the blacklists

Download the latest blacklists from the following sources:

-[http://github.com/IQAndreas/php-spam-filter](http://github.com/IQAndreas/php-spam-filter) (Most of the blacklists are from here)

Merged into one:

- [https://github.com/matomo-org/referrer-spam-list/blob/master/spammers.txt](https://github.com/matomo-org/referrer-spam-list/blob/master/spammers.txt) (Referrer spam list)
- [https://github.com/ddofborg/analytics-ghost-spam-list/blob/master/adwordsrobot.com-spam-list.txt](https://github.com/ddofborg/analytics-ghost-spam-list/blob/master/adwordsrobot.com-spam-list.txt) (Referrer spam list)

## Create a new blacklist

- Any regular expression syntax can be used here (without the delimiters).
- Regular expressions are always case insensitive.
- No regular expression may span more than one line.
- Anything after the '#' character until the end of a line is ignored, so use this for comments.
- If you only want to write out keywords, remember to escape special characters that have meaning in regex.
