#/bin/bash

NEXT_VERSION=$1
CURRENT_VERSION=$(cat composer.json | grep version | head -1 | awk -F= "{ print $2 }" | sed 's/[version:,\",]//g' | tr -d '[[:space:]]')

sed -i "s/\"version\": \"$CURRENT_VERSION\"/\"version\": \"$NEXT_VERSION\"/g" composer.json

sed -i "s/Version:           $CURRENT_VERSION/Version:           $NEXT_VERSION/g" lexo-pages-order.php

sed -i "s/Stable tag: $CURRENT_VERSION/Stable tag: $NEXT_VERSION/g" readme.txt

sed -i "s/\"version\": \"$CURRENT_VERSION\"/\"version\": \"$NEXT_VERSION\"/g" info.json
sed -i "s/v$CURRENT_VERSION/v$NEXT_VERSION/g" info.json
sed -i "s/$CURRENT_VERSION.zip/$NEXT_VERSION.zip/g" info.json

npx mix --production
sudo composer dump-autoload -oa

mkdir -p lexo-pages-order

cp -r assets lexo-pages-order
cp -r languages lexo-pages-order
cp -r dist lexo-pages-order
cp -r src lexo-pages-order
cp -r vendor lexo-pages-order
cp -r ./*.php lexo-pages-order
cp LICENSE lexo-pages-order
cp readme.txt lexo-pages-order
cp README.md lexo-pages-order
cp CHANGELOG.md lexo-pages-order

zip -r ./build/lexo-pages-order-$NEXT_VERSION.zip lexo-pages-order -q
