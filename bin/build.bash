#!/usr/bin/env bash

composer update

[ -d htdocs/img ] || mkdir htdocs/img/
rsync -av vendor/mos/cimage/webroot/imgd.php webroot/img/imgd.php
