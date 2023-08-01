# Timetable checking for USTH

## Description
This project was made to handle the unexpected changes in timetable of USTH. This can also be used to fetch the changes of other ical feeds, but trust me, you don't want to use this for other purposes.

## How to use
Clone this repo, require the necessary packages and run the `fetcher.php` file. Make `.env` file based on `.env.example`. Perform the cronjob in your server to run this file periodically based on your need.

## How to get the timetable
You can follow the instruction to get the iCal here: https://support.pushpay.com/s/article/How-do-I-get-an-iCal-feed-from-Google-Calendar

## How to configure Telegram bot
You can follow the instruction here: https://core.telegram.org/bots#how-do-i-create-a-bot.
After creating the bot, you can get the chat id by sending a message to the bot and then go to this link: https://api.telegram.org/bot(YourBOTToken)/getUpdates

## For B3 (2023-2024) ICT students
You can follow this telegram channel to get the timetable: https://t.me/+Z9jgsx_3MeZkODA1

## Google Cloud Function
If you are going to use Google Cloud Function, you can deploy a cloud run instance and then set up a cronjob to call the endpoint periodically. I personally deployed with Serverless Function. You can check `fetcher_GCP_function.php`