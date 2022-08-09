<?php

/**
 * Send notifications for incoming messages in Chatwoot to a Slack channel
 *
 * 1. Put this on a webserver where Chatwoot is able to reach it
 * 2. Edit the settings below and create a Slack webhook (https://slack.com/help/articles/115005265063-Incoming-webhooks-for-Slack)
 * 3. Run: composer install
 * 4. Add a webhook in Chatwoot (Settings -> Integrations -> Webhooks -> Configure -> Add new webhook) with these settings:
 *     - Webhook URL: https://yourwebserver/chatwoot-slack-notification/incoming.php
 *     - Events: Message created
 * 5. Send a test message and watch it coming into Slack!
 *
 * Martijn Smit (@smitmartijn)
 * Version: 1.0
 */
include "vendor/autoload.php";

// what's the maximum Slack message size we want?
$max_slack_message_size = 200;
// what's the main Slack webhook URL? See line 54 for another (optional) webhook config
$webhook_url = 'https://hooks.slack.com/services/yoururl';
// Slack bot settings
$settings = [
  'username' => 'Chatwoot',
  'link_names' => true,
  'icon' => ':chatwoot:',
];

// parse incoming payload from chatwoot and format message
// format of chatwoot: https://www.chatwoot.com/docs/product/features/webhooks
$data = file_get_contents('php://input');
$json = json_decode($data, true);

// ignore outgoing messages, we only want notifications when someone replies to us
if (array_key_exists("message_type", $json)) {
  if ($json["message_type"] == "outgoing") {
    return;
  }
}

// the event field is a goldmine, you can also do things when a conversation is resolved or otherwise modified
// for this use case, we're only interested in new messages to a new or existing conversation
if ($json['event'] == "message_created") {
  $chat_message = $json['conversation']['messages'][0]['content'];
  $conversation_id = $json['conversation']['messages'][0]['conversation_id'];
  // truncate message if it's too long
  if (strlen($chat_message) > $max_slack_message_size) {
    $chat_message = substr($chat_message, 0, $max_slack_message_size) . "...";
  }

  // make sure the message is in a blockquote
  // the Slack API stops the blockquote when a newline is detected, so go through all
  // lines and prefix them with a '>' to get it blockquoted (sp?)
  $message_lines = explode("\n", $chat_message);
  $chat_message = "";
  foreach ($message_lines as $line) {
    $chat_message .= "> " . $line . "\n";
  }

  $message = "New message from " . $json['sender']['name'] . "\n";
  $message .= $chat_message . "\n";
  $message .= "https://chat.whatpulse.org/app/accounts/1/conversations/" . $conversation_id;
} else {
  // you can check for other events here, if you want
  return;
}

// override webhook url for a specific inbox
// I left this in as an example, but if you want everything to go to one
// Slack channel, just remove this block
$inbox_id = intval($json['conversation']['inbox_id']);
if ($inbox_id == 12 || $inbox_id == 13) {
  $webhook_url = "https://hooks.slack.com/services/yourURL";
}

// send message to Slack!
$client = new Maknz\Slack\Client($webhook_url, $settings);
$client->send($message);
