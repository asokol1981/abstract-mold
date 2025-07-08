#!/bin/bash
trap "exit 0" SIGTERM

sleep infinity &
wait
