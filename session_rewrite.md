Session rewrite
================

### What's this? ###

Sessions are a mechanism of storing user-data between requests. They are very useful in a lot of situations: login forms, success or warning messages (Ruby on Rails does this) and more. PHP has its own (basic) implementation of sessions, but it has its problems. The code provided here aims at fixing a few of the more problematic problems.

The goal of this document is to provide you with a bit of insight on to why these classes are needed, and how you can benefit from them in your own websites, web-apps and extensions.

### TL;DR ###

The code presented here will make caching Symphony output easier, and it will scaling to more than one web/database server easier.

### Problems with sessions as they are ###

As mentioned earlier, PHP has its own Session implementation. Simply put, when you call `session_start()`, a `PHPSESSID` cookie is set on the user, which will expire as soon as the browser closes. After calling the function, the `$_SESSION` array will persist between requests, so you can store data you wish to keep inside the array.

#### Session Storage ####

There are (at least) two problems with this. The first one is that internally, PHP will store a textfile in a temporary directory, which contains the session data you are saving. Now, if you are running a single webserver, this is fine - reading a single txt-file is not going to be a bottleneck, and assuming you've configured your webserver correctly, the files are not world-readable, so the session data will stay (relatively) secure on the server.

However, as soon as the number of visitors starts to grow, and you deploy more than one webserver to handle the traffic, you will start to run into problems. Since the session information is stored locally on each server, this means that if request 1 is served by webserver `A`, and the next request is served by webserver `B`, the session is lost.

To solve this problem, you can do a few things. The simplest solution is to make each request **stick** to a webserver. If your first request was handled by server `A`, then each next request should also be handled by server `A`. This works very well, and most load balancers support this (one way or another). However, as usual, the easy solution has its drawbacks. Since your requests will always be routed to the same server, you will not be able to use the website if your server goes down, even if there are plenty of other servers that are up.

Another solution is to store the session data somewhere where both servers can access it. This could be a network storage (slow!) or a database (MySQL, MemCached and Redis are often used for this). The upside of this is that databases are very good (fast!) at simply looking up values. They are mostly also designed with replication and high-availability in mind, so the risk of losing session data is reduced.

The classes provided here will try to solve this problem by saving the cookie data in a database of some kind. The aim is to support more than one type of database, as the data type lends intself perfectly for noSQL databases. However, since this code will be written to improve session handling within Symphony, MySQL will be the first database provider to be developed.

#### Sessions and caching ####

Another issue with sessions, and in particular the cookies used for them, is that they prevent effective caching. In most cases caching will speed up your website or app immensely. With Symphony, response times of 500ms or more aren't unusual. Most of this time is spent getting the most recent articles, grabbing the number of comments for each article, and so on. Then out of this data your webpage is rendered. The server will do this for every. single. request. So even if you visit the same page within a few seconds (when you are sure nothing has changed in between), it will still do all this work.

A caching layer (like Varnish or Squid) prevents this problem by storing the output of a request, and just serving that content for everyone who requests it afterwards. This will make your site screaming fast. Response times of 10ms are not unusual, and handling hundreds(!) of users simultaneously with a single server is not a big deal (if you have enough bandwidth, that is). So, long story short, caching is awesome.

However, I wouldn't by writing this if there wasn't a but. So, here it is: not everything can be cached. If your content changes for each visitor, then caching the response will not make things faster. Imagine being logged in to your website, then you will probably want to show a `log out` button somewhere, which should not be there if you aren't logged in.

You probably see where I am going with this: sessions/cookies handle logins. Because of this, **caching layers will not cache a page when it is requested with a cookie.** By itself this is not really a problem: you could still cache all anonymous requests, and letting all logged-in people hit the backend servers. However, the problem is this: **by default PHP's session handler will set a session cookie on every request**, making even anonymous requests impossible to cache.

To solve this problem, we should only set the cookie if there is actual data inside the session we want to keep. That way every anonymous user will get the cached copy, and every logged in user will get their own personalised version. The code presented here will do exactly this.

#### Where to go from here ####

While this should be a step forward, handling session data is still a tricky business. Not every extension developer will create extensions with cacheability in mind, so there will still be cases where data that should be stored in a cookie will be stored in a session (language info, for instance). Providing a clean API for both types of storage, and educating developers is the only way to solve this problem. This, however, is not easy.