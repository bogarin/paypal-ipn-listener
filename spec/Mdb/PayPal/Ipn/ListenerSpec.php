<?php

namespace spec\Mdb\PayPal\Ipn;

use Closure;
use Mdb\PayPal\Ipn\Event\MessageVerificationEvent;
use Mdb\PayPal\Ipn\Listener;
use Mdb\PayPal\Ipn\Message;
use Mdb\PayPal\Ipn\MessageFactory;
use Mdb\PayPal\Ipn\Verifier;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ListenerSpec extends ObjectBehavior
{
    function let(
        MessageFactory $messageFactory,
        Message $message,
        Verifier $verifier,
        EventDispatcher $eventDispatcher
    )
    {
        $messageFactory->createMessage()->willReturn($message);

        $this->beConstructedWith(
            $messageFactory,
            $verifier,
            $eventDispatcher
        );
    }

    function it_dispatches_an_event_when_a_message_is_verified(
        Verifier $verifier,
        EventDispatcher $eventDispatcher
    )
    {
        $verifier->verify(
            Argument::type('Mdb\PayPal\Ipn\Message')
        )->willReturn(true);

        $eventDispatcher->dispatch(
            Listener::IPN_VERIFIED_EVENT,
            Argument::type('Mdb\PayPal\Ipn\Event\MessageVerifiedEvent')
        )->shouldBeCalled();

        $this->listen();
    }

    function it_dispatches_an_event_when_a_message_is_invalid(
        Verifier $verifier,
        EventDispatcher $eventDispatcher
    )
    {
        $verifier->verify(
            Argument::type('Mdb\PayPal\Ipn\Message')
        )->willReturn(false);

        $eventDispatcher->dispatch(
            Listener::IPN_INVALID_EVENT,
            Argument::type('Mdb\PayPal\Ipn\Event\MessageInvalidEvent')
        )->shouldBeCalled();

        $this->listen();
    }

    function it_dispatches_an_event_when_it_fails_to_verify_a_message_due_to_an_unexpected_value_being_returned(
        Verifier $verifier,
        EventDispatcher $eventDispatcher
    )
    {
        $verifier->verify(
            Argument::type('Mdb\PayPal\Ipn\Message')
        )->willThrow('UnexpectedValueException');

        $eventDispatcher->dispatch(
            Listener::IPN_VERIFICATION_FAILURE_EVENT,
            Argument::type('Mdb\PayPal\Ipn\Event\MessageVerificationFailureEvent')
        )->shouldBeCalled();

        $this->listen();
    }

    function it_dispatches_an_event_when_it_fails_to_verify_a_message_due_to_a_failure_communicating_with_api(
        Verifier $verifier,
        EventDispatcher $eventDispatcher
    )
    {
        $verifier->verify(
            Argument::type('Mdb\PayPal\Ipn\Message')
        )->willThrow('Mdb\PayPal\Ipn\Exception\ApiRequestFailureException');

        $eventDispatcher->dispatch(
            Listener::IPN_VERIFICATION_FAILURE_EVENT,
            Argument::type('Mdb\PayPal\Ipn\Event\MessageVerificationFailureEvent')
        )->shouldBeCalled();

        $this->listen();
    }

    function it_can_attach_a_listener_for_the_message_verified_event(
        Verifier $verifier,
        EventDispatcher $eventDispatcher
    )
    {
        $eventDispatcher->addListener(
            Listener::IPN_VERIFIED_EVENT,
            Argument::type('callable')
        )->shouldBeCalled();

        $this->onVerified(function (IpnMessageEvent $event) {});
    }

    function it_can_attach_a_listener_for_the_message_invalid_event(
        Verifier $verifier,
        EventDispatcher $eventDispatcher
    )
    {
        $eventDispatcher->addListener(
            Listener::IPN_INVALID_EVENT,
            Argument::type('callable')
        )->shouldBeCalled();

        $this->onInvalid(function (MessageVerificationEvent $event) {});
    }

    function it_can_attach_a_listener_for_the_message_verification_failure_event(
        Verifier $verifier,
        EventDispatcher $eventDispatcher
    )
    {
        $eventDispatcher->addListener(
            Listener::IPN_VERIFICATION_FAILURE_EVENT,
            Argument::type('callable')
        )->shouldBeCalled();

        $this->onVerificationFailure(function (MessageVerificationEvent $event) {});
    }
}