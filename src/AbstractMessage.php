<?php

namespace Sms;

abstract class AbstractMessage implements MessageInterface
{
	const HEADER_PARAMETERS = [];

	/**
	 * {@inheritDoc}
	 */
	public function getTo()
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function getFrom()
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function getMessage()
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function getHeader($header)
	{

	}

	public function validateHeader($header, $content)
	{
		// Validate against self::HEADER_PARAMETERS
		// if false, log error?
		return true;
	}
}
