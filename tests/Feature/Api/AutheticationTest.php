<?php

it('has api/authetication page', function () {
    $response = $this->get('/api/authetication');

    $response->assertStatus(200);
});
