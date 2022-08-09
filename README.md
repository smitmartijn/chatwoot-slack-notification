# chatwoot-slack-notification

Incoming webhook receiver that forwards Chatwoot notifications to a Slack channel

## Instructions

1. Put this on a webserver where Chatwoot is able to reach it
2. Edit the settings below and create a Slack webhook (https://slack.com/help/articles/115005265063-Incoming-webhooks-for-Slack)
3. Run: composer install
4. Add a webhook in Chatwoot (Settings -> Integrations -> Webhooks -> Configure -> Add new webhook) with these settings:
  - Webhook URL: https://yourwebserver/chatwoot-slack-notification/incoming.php
  - Events: Message created
5. Send a test message and watch it coming into Slack!
