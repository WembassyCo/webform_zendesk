CONTENTS OF THIS FILE
---------------------

* Introduction
* Installation
* Configuration

INTRODUCTION
------------

The Webform Zendesk module adds a webform plugin to allow mapping webform fields to Zendesk tickets in Drupal 8.

* The primary use case for this module is to:

- **Build** a new webform plugin to allow integration between zendesk and webform module.
- **Collect** the module collects the webform submissions and post it to zendesk as tickets.

INSTALLATION
------------

The installation of this module is like other Drupal modules.

1. Copy/upload the webform zendesk module to the modules directory of your Drupal
   installation.

2. Enable the 'Webform zendesk' module in 'Extend'.
   (/admin/modules)
   

CONFIGURATION
-------------

* Configure zendesk API credentials at /admin/config/services/webform_zendesk path. The configuration form consists API Token, Zendesk Username & Zendesk subdomain fields.
  You need to enter these details before using the plugin.
* Build a new webform (/admin/structure/webform)
  or duplicate an existing template (/admin/structure/webform/templates).
* Enable the "Create a Zendesk Issue" webform plugin".
* The module have support for custom fields on zendesk. You need to map the custom fields from zendesk to webform fields.
* Add this mapping in "Custom Fields Mapping" field in the plugin. The mapping must follow this pattern.
* Lets say we have two fields Pize & Product in webform whose machine names are "size" & "product". so the mapping should be <br />
  size:3600123453,<br />
  product:3600321345 <br />
  Here the numeric value is the field id on zendesk.
  
* The select fields on webform must have same key value as it is on zendesk field.
  <br />

