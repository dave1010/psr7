<?php
namespace GuzzleHttp\Psr7;

use Psr\Http\Message\StreamableInterface;

/**
 * Trait implementing functionality common to requests and responses.
 */
trait MessageTrait
{
    /** @var array Cached HTTP header collection with lowercase key to values */
    private $headers = [];

    /** @var array Actual key to list of values per header. */
    private $headerLines = [];

    /** @var string */
    private $protocol = '1.1';

    /** @var StreamableInterface */
    private $stream;

    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    public function withProtocolVersion($version)
    {
        $new = clone $this;
        $new->protocol = $version;
        return $new;
    }

    public function getHeaders()
    {
        return $this->headerLines;
    }

    public function hasHeader($header)
    {
        return isset($this->headers[strtolower($header)]);
    }

    public function getHeader($header)
    {
        $name = strtolower($header);

        return isset($this->headers[$name])
            ? implode(', ', $this->headers[$name])
            : '';
    }

    public function getHeaderLines($header)
    {
        $name = strtolower($header);
        return isset($this->headers[$name]) ? $this->headers[$name] : [];
    }

    public function withHeader($header, $value)
    {
        $new = clone $this;
        $header = trim($header);
        $name = strtolower($header);

        if (!is_array($value)) {
            $new->headers[$name] = [trim($value)];
        } else {
            foreach ($value as $v) {
                $new->headers[$name][] = trim($v);
            }
        }

        // Remove the header lines.
        foreach (array_keys($new->headerLines) as $key) {
            if (strtolower($key) === $name) {
                unset($new->headerLines[$key]);
            }
        }

        // Add the header line.
        $new->headerLines[$header] = $new->headers[$name];

        return $new;
    }

    public function withAddedHeader($header, $value)
    {
        if (is_array($value)) {
            $current = array_merge($this->getHeaderLines($header), $value);
        } else {
            $current = $this->getHeaderLines($header);
            $current[] = (string) $value;
        }

        return $this->withHeader($header, $current);
    }

    public function withoutHeader($header)
    {
        if (!$this->hasHeader($header)) {
            return $this;
        }

        $new = clone $this;
        $name = strtolower($header);
        unset($new->headers[$name]);

        foreach (array_keys($new->headerLines) as $key) {
            if (strtolower($key) === $name) {
                unset($new->headerLines[$key]);
            }
        }

        return $new;
    }

    public function getBody()
    {
        if (!$this->stream) {
            $this->stream = Stream::factory('');
        }

        return $this->stream;
    }

    public function withBody(StreamableInterface $body)
    {
        $new = clone $this;
        $new->stream = $body;
        return $new;
    }

    private function setHeaders(array $headers)
    {
        $this->headerLines = $this->headers = [];
        foreach ($headers as $header => $value) {
            $header = trim($header);
            $name = strtolower($header);
            if (!is_array($value)) {
                $value = trim($value);
                $this->headers[$name][] = $value;
                $this->headerLines[$header][] = $value;
            } else {
                foreach ($value as $v) {
                    $v = trim($v);
                    $this->headers[$name][] = $v;
                    $this->headerLines[$header][] = $v;
                }
            }
        }
    }
}
