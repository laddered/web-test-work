# README #

This is a test work from Web Department of Unigine Company.

### How do I get set up? ###

* Install Docker Compose
* Install Git
* Clone this repository
* Put ```127.0.0.1 url-shortener.loc``` into your hosts file
* Run ```docker-compose up``` in the root of the repository
* Go to ```http://url-shortener.loc``` in your browser

### How do I use it? ###

* To encode ```someurl``` you can use ```/encode-url?url=someurl``` endpoint
* To decode ```somehash``` you can use ```/decode-url?hash=somehash``` endpoint
* To redirect ```somehash``` you can use ```/go-url?hash=somehash``` endpoint


* To send new urls data you can use ```php bin/console app:send-new-urls-data``` symfony command
* To get statistics for a url between two dates in format ```Y-m-d_H:i:s``` you can use ```/urls/statistics/range?start_date=Y-m-d_H:i:s&end_date=Y-m-d_H:i:s``` endpoint
* To get statistics on a specific domain url ```example.com``` you can use ```/urls/statistics/domain?domain=example.com``` endpoint
