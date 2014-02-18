Aesthetics oDesk App
====================

Overview
--------
Aesthetics oDesk App is an experimental application using oDesk API to post jobs, manage recruiting and conclude contracts and payments.
It is developed to accommodate Aesthetics project, a project for academic purposes.

During the development process some <b>oDesk API</b> comment and observations will/might be logged to help understand and/or improve oDesk API.


Requirements
------------

//TODO

Installation
------------ 

//TODO

Build overview
--------------

//TODO

Usage overview
--------------

//TODO

oDesk API Tips/Comments
---------------------
-- Listing objects through the API (jobs, offers, etc): Response is not always a collection of objects (array of objects). If the response has one object it should be AGAIN an
array with ONE object. This helps a lot in template building to avoid unnecessary if clause and template duplicates (code repetition!!!).

-- Listing objects through the API (jobs, offers, etc) don't provide total to implement pagination.
It only provides total of requested <u>offset;count</u>. Thus only fragmented / partial data loading is possible.

-- Online job posting accept budget of 5 usd minimum (for fixed price jobs), while through the API less budget can be accepted?!

-- Message center: when sending messages via API messages appear well in oDesk site but for some reason they are delivered with new lines ("\n") replaced by breaks (br).

<b>more coming ... </b> 