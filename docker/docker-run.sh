#!/bin/bash
docker-compose up -d

echo "wait for init..."; sleep 3; echo "OK";

docker-compose exec bank_ocr_php sh /tmp/composer-installer.sh
docker cp /usr/share/zoneinfo/Europe/Warsaw bank_ocr_php:/etc/localtime

echo "Done."
