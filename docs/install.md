# Installation

If you want to install `360top` via an automated mechanism you are able to hand over all parameters via the cli.

### Initialize with agent360 installed

If the 360 Monitoring agent is already installed you can tell the init script it should fetch the server ID directly
from the agent configuration by adding the `--fromAgentConfig` option.

```shell
360top init --fromAgentConfig --token <apiToken>
```

### Install via Ansible

```yaml
    - name: Install 360top
      shell: |
        wget https://github.com/leankoala-gmbh/360top/releases/latest/download/360top.phar && chmod +x 360top.phar && sudo mv 360top.phar /usr/local/bin/360top
      args:
        executable: /bin/bash

    - name: Init 360top
      shell: |
        360top init --fromAgentConfig --token c394be60e6a4e526b7bdbb8de35507a9806a12e2259a8adc8edd5554f64403fa
      args:
        executable: /bin/bash
```
