CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Notices

INTRODUCTION
------------

The Rest APi Demo module provides support functions for create content of default content type 'basic page' with additional tags term reference field & 'publish date' field using REST API resource.

REQUIREMENTS
------------

This module requires the following module:

 * Rest UI - https://www.drupal.org/project/restui


INSTALLATION
------------

 * Install as you would normally install as per a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.

CONFIGURATION
-------------

 1. Configure the module at Administration > Configuration > Web Services > REST
    (/admin/config/services/rest). 
 2. Enable Resource Name "Rest Resource Post Example Demo".
 3. Select Granularity to "Resource", check Methods to "POST", Accepted request formats to "json" & Authentication providers to "basic_auth".
 4. Click "save configuration" to save configuration. 
 5. Goto Administration > Structure > Content types > Basic page (admin/structure/types/manage/page/fields), create 2 fields with name "Tags" with term_reference type (machine name is "field_tags" ) & "Publish Date" with Date type & select Date type is "Date and time" (machine name is "field_publish_date"). 


NOTICES
-------

1. Check result using path "/rest/api/post/node-create/page?_format=json"

2. Type & title is required field.
example 1: send request (without type)-

{  "type": {
    "value": ""
  },
  "title": {
    "value": "My Article 7"
  },
  "body": {
    "value": "some body content aaa bbb ccc",
    "format": "full_html"
  },
  "field_tags": {
    "name": "month"
  },
  "field_publish_date": {
    "datetime": "05/20/2021 13:45:00"
  }
}
Response - 
{
    "status": "failure",
    "error": "Type field is missing."
}

Example 2 : send request -

{  
  "type": {
    "value": "page"
  },
  "title": {
    "value": "My Basic page"
  },
  "body": {
    "value": "Test body",
    "format": "full_html"
  },
  "field_tags": {
    "name": "May"
  },
  "field_publish_date": {
    "datetime": "05/22/2021 13:45:00"
  }
}
Response -

{
    "status": "success",
    "message": "Content with title My Basic page has been created successfully."
}
