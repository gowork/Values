#!/bin/bash

function spec() {
  ./bin/phpspec run
}

function unit() {
  ./bin/phpunit tests
}

function stan() {
  ./bin/phpstan analyse src examples tests/GenericCases --level=8
}

function psalm() {
  ./bin/psalm
}

function coverage() {
  phpdbg -qrr bin/phpspec run --config=phpspec-cov.yml
}

function benchmarks() {
  ./bin/phpbench run benchmarks --report=values --output=md --retry-threshold=5 --revs=1000 -vv
}

function main() {
  case "$1" in
    spec)
      spec
      ;;
    unit)
      unit
      ;;
    stan)
      stan
      ;;
    psalm)
      psalm
      ;;
    coverage)
      coverage
      ;;
    benchmarks)
      benchmarks
      ;;
    *)
      spec && unit && stan && psalm
      ;;
  esac
}

main "$@"
