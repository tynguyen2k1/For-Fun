#!/bin/bash

for ((i = $1; i < $2 ; i++)); do
    php ./index.php $i
done
