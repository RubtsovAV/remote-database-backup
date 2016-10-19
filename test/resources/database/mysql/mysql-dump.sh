#!/bin/bash

/docker-entrypoint-initdb.d/bin/join.sh | "${mysql[@]}"