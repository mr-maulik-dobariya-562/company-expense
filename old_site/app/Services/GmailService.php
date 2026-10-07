<?php

namespace App\Services;

use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;
use App\Models\EmailThread;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GmailService
{
	private $client;
	/**
	 * @var Gmail
	 */
	protected $gmail;
	protected $view;
	protected $ccEmails = [];
	protected $toMails = [];
	protected $attachments = [];
	protected $threadId = null;

	public function configureGmailService()
	{
		$this->client = new Client();
		$this->client->setApplicationName('Gmail API PHP Quickstart');
		$this->client->setAuthConfig(json_decode(Storage::get('credentials/credentials.json'), true));
		$this->client->setScopes(Gmail::MAIL_GOOGLE_COM);
		$this->client->setAccessType('offline');
		$this->client->setPrompt('select_account consent');


		if (Storage::exists('credentials/token.json')) {
			$this->client->setAccessToken(json_decode(Storage::get('credentials/token.json'), true));
		}


		if ($this->client->isAccessTokenExpired()) {
			$refreshToken = $this->client->getRefreshToken();
			if ($refreshToken) {

				$newAccessToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);

				Storage::put('credentials/token.json', json_encode($newAccessToken));
				$this->client->setAccessToken($newAccessToken);
			} else {

				//
				return redirect()->away($this->client->createAuthUrl())->send();
			}
		}

		$this->gmail = new Gmail($this->client);
	}

	/**
	 * Check if an existing thread exists for given recipients.
	 *
	 * @param array $recipients
	 * @return string|null
	 */
	public function checkExistingThread($recipients)
	{
		// Check if all emails match any existing thread
		$existingThread = EmailThread::whereJsonContains('recipients', $recipients)->first();
		return $existingThread ? $existingThread->thread_id : null;
	}


	public function setCcEmails($emails = [])
	{

		$this->ccEmails = $emails;
		return $this;
	}

	public function setToMails($emails)
	{

		$this->toMails = $emails;
		return $this;
	}

	public function setThreadId($id)
	{
		$this->threadId = $id;

		return $this;
	}


	public function addAttachments(array $attachments)
	{


		// Expects array of full file paths or storage paths
		$this->attachments = $attachments;
		return $this;
	}

	public function getThreadId()
	{

		return $this->threadId;
	}
	/**
	 * Send an email via Gmail API.
	 *
	 * @param string $subject
	 * @param string $messageText
	 * @return Message|string|self
	 */

	public function sendEmail(string $subject, string $messageText)
	{
		try {
			$this->configureGmailService();

			$service = $this->gmail;

			$recipients = $this->toMails;

			$rawMessage = "To: " . implode(', ', $recipients) . "\r\n";
			$rawMessage .= "Subject: {$subject}\r\n";
			$rawMessage .= "MIME-Version: 1.0\r\n";


			$boundary = uniqid(rand(), true);


			try {
				if ($this->threadId) {

					$thread = $service->users_threads->get('me', $this->threadId);
					$messages = $thread->getMessages();
					$lastMessage = end($messages);
					$payload = $lastMessage->getPayload();
					$headers = $payload->getHeaders();

					foreach ($headers as $header) {
						if ($header->getName() === 'Message-Id') {
							$messageId = $header->getValue();
							$rawMessage .= "In-Reply-To: {$messageId}\r\n";
							$rawMessage .= "References: {$messageId}\r\n";
						}
					}
				} else {
					// print "No thread id found";
				}
			} catch (\Exception $e) {
				// print 'An error occurred: ' . $e->getMessage();
				Log::error('An error occurred: ' . $e->getMessage());

			}



			// Log::info("rawMessage: " .  $rawMessage);

			$this->ccEmails[] = "parshvabuildtechllp@gmail.com";

			if (!empty($this->ccEmails)) {
				$rawMessage .= "Cc: " . implode(', ', $this->ccEmails) . "\r\n";
			}
			// $rawMessage .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";

			// $rawMessage .= $messageText;

			// new attachment code

			if (!empty($this->attachments)) {
				$rawMessage .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n\r\n";
				// Add message body as a part
				$rawMessage .= "--{$boundary}\r\n";
				$rawMessage .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
				$rawMessage .= $messageText . "\r\n";
			} else {
				$rawMessage .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
				$rawMessage .= $messageText . "\r\n";
			}


			// Add attachments
			foreach ($this->attachments as $filePath) {
				if (!file_exists($filePath)) {
					Log::warning("Attachment file not found: {$filePath}");
					continue;
				}

				$fileContent = file_get_contents($filePath);
				$filename = basename($filePath);
				$mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

				$rawMessage .= "--{$boundary}\r\n";
				$rawMessage .= "Content-Type: {$mimeType}; name=\"{$filename}\"\r\n";
				$rawMessage .= "Content-Disposition: attachment; filename=\"{$filename}\"\r\n";
				$rawMessage .= "Content-Transfer-Encoding: base64\r\n\r\n";
				$rawMessage .= chunk_split(base64_encode($fileContent)) . "\r\n";
			}

			// Close the boundary if attachments were added
			if (!empty($this->attachments)) {
				$rawMessage .= "--{$boundary}--\r\n";
			}




			// $rawMessage .= "--$boundary--";

			$encodedMessage = base64_encode($rawMessage);
			$encodedMessage = str_replace(['+', '/', '='], ['-', '_', ''], $encodedMessage);

			$message = new Message();
			$message->setRaw($encodedMessage);
			if ($this->threadId) {
				$message->setThreadId($this->threadId);
			}

			$sentMessage = $service->users_messages->send('me', $message);
			$this->threadId = $sentMessage->getThreadId();
			return $this->threadId;
		} catch (\Exception $e) {
			Log::error("Gmail API Error: " . $e->getMessage());

			return null;
		}

	}
}
