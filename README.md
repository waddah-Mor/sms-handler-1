# Dockerized RESTful interface for SMS Server Tools

Relatively featureless experimentation and part of a hacky solution to a problem.

TODO (will update as necessary):
* Add a few new methods to API (commented in code), then,
* Version up the API,
* Document said API,
* Sort port binding so it's not fixed to 80 internally,
* Implement environment variables for testing and more
* Build out tests for both the API and Gateway (laziness now will sting me later in life, ahhwell),
* Integrate with [Travis CI](https://travis-ci.org/)
* Add in/throw numerous exceptions:
	* Invalid _'to'_ phone number
	* Invalid _'body'_
	* Unable to _'expunge'_ read emails
* Address users global requirements for make (Composer).
* <sub><small>Expand the Makefile</small></sub>
* Merge My SMS lib and this into one project with a _proper_ build out

### Building Image from GitHub

If you're pulling directly from the Docker Hub you can skip straight to _Running Containers_

This will require [Composer](https://getcomposer.org/) to be installed globally.

```
git clone git@github.com:itsliamjones/sms-handler.git sms-handler
cd sms-handler
make
```

### Running Containers

If you've compiled from GitHub, please substitute your image tag in on build commands.

```
docker run -d \
	--name sms-handler \
	-p 47563:80 \
    -v /var/spool/sms/:/var/spool/sms/ \
    sms-handler
```