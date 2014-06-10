from lib import junitparser
import os.path as path
import glob
import os




def summary(results):
    """
    Takes a list of (test_suite, test_results) tuples, and summarises it into a list of
    dicts in the following format:
    [{
      'suite': 'tests.ui.test_foo_bar',
      'result': 'failure',
      'tests': {
        'test_foo_get': 'success',
        'test_foo_get': 'failure',
      }
    }, ...]
    """
    summary = []
    for result in results:
        suite, stats = result
        tests = suite._tests
        success = len(stats.errors) == 0 and len(stats.failures) == 0
        test_results = {}
        summary.append({
          "suite": tests[0].classname,
          "result": ("success" if success else "failure"),
          "tests": dict((test.methodname, test.result) for test in tests)
        })
    return summary

def parse(paths):
    results = []
    files = to_files(paths)
    for f in files:
        with open(f) as source:
            results.append(junitparser.parse(source))
    return results

def to_files(paths):
    files = set()
    for p in paths:
        if not path.exists(p):
            raise ValueError("%s does not exist" % p)
        if path.isdir(p):
            for d in glob.glob(path.join(p, '*.xml')):
                files.add(path.abspath(d))
        else:
            files.add(path.abspath(p))
    return files
