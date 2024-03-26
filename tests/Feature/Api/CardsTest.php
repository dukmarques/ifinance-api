<?php

it('has api/cards page', function () {
    $response = $this->get('/api/cards');

    $response->assertStatus(200);
});
