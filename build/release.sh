#/bin/bash

NEXT_VERSION=$1
CURRENT_VERSION=$(cat composer.json | grep version | head -1 | awk -F= "{ print $2 }" | sed 's/[version:,\",]//g' | tr -d '[[:space:]]')

sed -ie "s/\"version\": \"$CURRENT_VERSION\"/\"version\": \"$NEXT_VERSION\"/g" composer.json
rm -rf composer.jsone

sed -ie "s/Version:           $CURRENT_VERSION/Version:           $NEXT_VERSION/g" lexo-pages-order.php
rm -rf lexo-pages-order.phpe

sed -ie "s/Stable tag: $CURRENT_VERSION/Stable tag: $NEXT_VERSION/g" readme.txt
rm -rf readme.txte

sed -ie "s/\"version\": \"$CURRENT_VERSION\"/\"version\": \"$NEXT_VERSION\"/g" info.json
rm -rf info.jsone

sed -ie "s/v$CURRENT_VERSION/v$NEXT_VERSION/g" info.json
rm -rf info.jsone

sed -ie "s/$CURRENT_VERSION.zip/$NEXT_VERSION.zip/g" info.json
rm -rf info.jsone

npx mix --production
sudo composer dump-autoload -oa

mkdir lexo-pages-order

cp -r assets lexo-pages-order
cp -r languages lexo-pages-order
cp -r dist lexo-pages-order
cp -r src lexo-pages-order
cp -r vendor lexo-pages-order
cp ./*.php lexo-pages-order
cp LICENSE lexo-pages-order
cp readme.txt lexo-pages-order
cp README.md lexo-pages-order
cp CHANGELOG.md lexo-pages-order

zip -r ./build/lexo-pages-order-$NEXT_VERSION.zip lexo-pages-order -q
