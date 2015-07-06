
You can even start whole test without config files, just from switches. 
Like this to launch existing JMX with no modifications:
```
bzt -o execution.scenario.jmx=my_plan.jmx
```

There is a way to create some config chunks and apply them from command-line like this: `bzt -gui-mode -scenario1`
Those aliases then searched in the config, in the section `cli-aliases` and applied over the configuration. Example:

```yaml
---
cli-aliases:
  gui-mode:
    modules:
      jmeter:
        gui: true
  scenario1:
    scenarios:
      my-scen:
        script: jmx2.jmx
```
