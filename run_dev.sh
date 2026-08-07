#!/bin/bash

# NPM Watch
gnome-terminal -- bash -c "npm run watch; exec bash"

# SASS Watch
gnome-terminal -- bash -c "npx sass --watch .:.; exec bash"

# Useful Commands:
# pg_dump -h localhost -p 5433 -U postgres -F c -b -v -f backup.dump pcexpert