#IntegratedMenuBundle#
This bundle provides some extra functionality for the [KnpMenuBundle](https://github.com/KnpLabs/KnpMenuBundle).

## Operations Runbook

### Purpose
- Extend KnpMenu with event-driven menu contributions
- Provide database-backed menu providers for editable navigation

### Commands
This bundle does not expose standalone console commands.

### Cron And Workers
No dedicated cron worker.

### Verification
- Confirm menu events are dispatched and listeners can extend admin/frontend navigation.
- Verify database-backed menu items render as expected in consuming bundles.

### Troubleshooting
- Missing menu items: verify provider registration and doctrine mapping for menu entities/documents.
- Frontend editing integration issues: verify WebsiteBundle and MenuBundle provider wiring.

##Features##
This bundle has the following features:

* MenuEvents for extending the navigation menu's
* Database provider to read navigation menu items from the database (can be used to allow front-end editing in combination with the website-bundle)

##Documentation##
* [Read the documentation at the Integrated for developers website](http://www.integratedfordevelopers.com)

##Installation##
The installation instructions can be found in the documentation.

##License##
This bundle is under the MIT license. See the complete license in the bundle `LICENSE`

## About ##
The IntegratedMenuBundle is part of the [Integrated project](http://www.integratedfordevelopers.com).
