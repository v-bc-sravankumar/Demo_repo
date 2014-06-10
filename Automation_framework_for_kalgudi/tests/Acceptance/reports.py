# -*- coding: utf-8 -*-
import lib.junit as junit
import sys

reports = junit.parse(sys.argv[1:])
suites = junit.summary(reports)

total_skipped = 0

print "Test results summary:"

for suite in suites:
    tests = suite["tests"]
    skipped = sum(x == "skipped" for x in tests.values())
    print "{0} - {1}: {2} ({3} tests skipped)".format(
        ("✓" if suite['result'] == 'success' else "✘"),
        suite['suite'],
        suite['result'],
        skipped
    )
    total_skipped += skipped

print "\n{0} test suites, {1} failures ({2} tests skipped)".format(
    len(suites),
    len([suite['result'] for suite in suites if suite['result'] == 'failure']),
    total_skipped
)
