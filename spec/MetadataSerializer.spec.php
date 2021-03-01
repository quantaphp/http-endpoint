<?php

declare(strict_types=1);

use Quanta\Http\MetadataSerializer;

describe('MetadataSerializer', function () {

    beforeEach(function () {
        $this->serializer = new MetadataSerializer('data', ['code' => 200, 'success' => true]);
    });

    describe('->__invoke()', function () {

        context('when the given value is encodable as json', function () {

            it('should return the given value as a key of the json content', function () {
                $test = ($this->serializer)(['k1' => 'v1', 'k2' => 'v2']);

                expect($test)->toEqual(json_encode([
                    'code' => 200,
                    'success' => true,
                    'data' => ['k1' => 'v1', 'k2' => 'v2'],
                ]));
            });

        });

        context('when the given value is not encodable as json', function () {

            it('should throw an exception', function () {
                $test = fn () => ($this->serializer)(tmpfile());

                expect($test)->toThrow();
            });

        });

    });

});
