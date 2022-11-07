def main(ctx):

  use_hetzner = False

  docker_images = [
#    "ubuntu1604",
    "ubuntu1804",
    "ubuntu2004",
    "debian9",
    "debian10"
  ]
  hetzner_images = [
    "debian-10",
    "ubuntu-18.04",
    "ubuntu-20.04"
  ]

  ############################################################################

  pipelines = [
    step_lint(),
  ]
  oses = hetzner_images if use_hetzner else docker_images
  for os in oses:
    if use_hetzner:
      pipelines.append(step_hetzner(os))
    else:
      pipelines.append(step_docker(os))

  if ctx.build.event == "tag":
    pipelines.append(step_publish(oses))
  return pipelines

def step_lint():
  return {
    "kind": "pipeline",
    "name": "linter",
    "steps": [
      {
        "name": "Lint",
        "image": "veselahouba/molecule",
        "commands": [
          "shellcheck_wrapper",
          "flake8",
          "yamllint .",
          "ansible-lint"
        ]
      }
    ]
  }

def step_docker(os):
  return {
    "kind": "pipeline",
    "depends_on": [
        "linter",
    ],
    "name": "molecule-%s" % os,
    "services": [
      {
        "name": "docker",
        "image": "docker:dind",
        "privileged": True,
        "volumes": [
          {
            "name": "dockersock",
            "path": "/var/run"
          },
          {
            "name": "sysfs",
            "path": "/sys/fs/cgroup"
          }
        ]
      }
    ],
    "volumes": [
      {
        "name": "dockersock",
        "temp": {}
      },
      {
        "name": "sysfs",
        "host": {
          "path": "/sys/fs/cgroup"
        }
      }
    ],
    "steps": [
      {
        "name": "Molecule test",
        "image": "veselahouba/molecule",
        "privileged": True,
        "volumes": [
          {
            "name": "dockersock",
            "path": "/var/run"
          },
          {
            "name": "sysfs",
            "path": "/sys/fs/cgroup"
          }
        ],
        "commands": [
          "sleep 30",
          "docker ps -a",
          "ansible --version",
          "molecule --version",
          "MOLECULE_IMAGE=geerlingguy/docker-%s-ansible" % os,
          "export MOLECULE_IMAGE",
          "molecule test --all"
        ]
      },
      {
        "name": "Slack notification",
        "image": "plugins/slack",
        "settings": {
          "webhook": {
            "from_secret": "slack_webhook"
          },
          "channel": "ci-cd",
          "template": "Molecule for `{{build.branch}}` failed. {{build.link}}"
        },
        "when": {
          "status": [
            "failure"
          ]
        }
      }
    ]
  }

def step_hetzner(os):
  return {
    "kind": "pipeline",
    "depends_on": [
        "linter",
    ],
    "name": "molecule-%s" % os,
    "steps": [
      {
        "name": "Molecule test",
        "image": "veselahouba/molecule",
        "environment": {
          "HCLOUD_TOKEN": {
            "from_secret": "HCLOUD_TOKEN"
          }
        },
        "commands": [
          "ansible --version",
          "molecule --version",
          "REF=$$(echo $DRONE_COMMIT_REF | awk -F'/' '{print $$3}'|sed 's/_/-/g')",
          "REPO_NAME=$$(echo $DRONE_REPO_NAME | sed 's/_/-/g')",
          "MOLECULE_IMAGE=%s" % os,
          "export MOLECULE_IMAGE REPO_NAME REF",
          "molecule test --all"
        ]
      }
    ]
  }

def step_publish(oses):
  deps = []
  for os in oses:
    deps.append("molecule-%s" % os)
  return {
    "kind": "pipeline",
    "depends_on": deps,
    "name": "publish",
    "steps": [
        {
          "name": "Publish to Galaxy",
          "image": "veselahouba/molecule",
          "environment": {
            "GALAXY_API_KEY": {
              "from_secret": "GALAXY_API_KEY"
            }
          },
          "commands": [
            "ansible-galaxy role import --api-key $${GALAXY_API_KEY} $${DRONE_REPO_OWNER} $${DRONE_REPO_NAME}"
          ]
        },
        {
          "name": "Slack notification",
          "image": "plugins/slack",
          "settings": {
            "webhook": {
              "from_secret": "slack_webhook"
            },
            "channel": "ci-cd",
            "template":
              "{{#success build.status}}" +
                  "Publish for `{{build.tag}}` succeeded." +
                  "{{build.link}}" +
                "{{else}}" +
                  "Publish for `{{build.tag}}` failed." +
                  "{{build.link}}"+
              "{{/success}}"
          },
          "when": {
            "status": [
              "success",
              "failure"
            ]
          }
        }
    ]
  }
