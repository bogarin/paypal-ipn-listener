<?php

namespace spec\Mdb\PayPal\Ipn;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MessageSpec extends ObjectBehavior
{
    function let()
    {
        $data = array(
            'foo' => 'bar',
            'baz' => 'quz'
        );

        $this->beConstructedWith($data);
    }

    function it_can_retrieve_a_property()
    {
        $this->get('foo')->shouldReturn('bar');
        $this->get('baz')->shouldReturn('quz');
    }

    function it_returns_an_empty_string_when_retrieving_a_non_existent_property()
    {
        $this->get('bar')->shouldReturn('');
    }

    function it_can_be_represented_as_a_string()
    {
        $this->__toString()->shouldReturn('foo=bar&baz=quz');
    }

    function it_url_encodes_property_values_when_represented_as_a_string()
    {
        $data = array(
            'foo' => 'foo + bar (baz)'
        );

        $this->beConstructedWith($data);

        $this->__toString()->shouldReturn('foo=foo+%2B+bar+%28baz%29');
    }
}