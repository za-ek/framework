#!/bin/bash
wget https://github.com/za-ek/zaek_start/archive/master.zip -O zaek_start.zip
unzip zaek_start
cp -r zaek_start-master/* ./
rm -rf zaek_start-master/*
composer install