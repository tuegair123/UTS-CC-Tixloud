#!/bin/bash
awslocal s3 mb s3://poster-konser
awslocal sqs create-queue --queue-name antrian-tiket
echo "S3 Bucket & SQS Queue created successfully!"