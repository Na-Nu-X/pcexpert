@echo off

REM NPM Watch
start "NPM Watch" cmd /k "npm run watch"

REM SASS Watch
start "SASS Watch" cmd /k "npx sass --watch .:."

exit