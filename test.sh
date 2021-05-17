#!/bin/bash

image='sdk-php'
tag='1.0.0'

if ! docker build . -t $image:$tag; then
  echo "We couldn't build the docker image to run the tests"

  exit 1
fi

if ! docker run $image:$tag; then
  echo "Something happened when running the tests"
fi
