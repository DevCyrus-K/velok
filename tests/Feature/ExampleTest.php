<?php

it('redirects guests from the dashboard root to login', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
