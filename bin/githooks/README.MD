Git Hooks
=============================

This folder contains the git hooks used the by this project. 
To install them, just copy the files "post-checkout", "post-commit"
and "prepare-commit-msg" to "$THIS_REPOSITORY_DIR/.git/hooks/".


Program usage: 
```
./bin/build.sh gatv [major | minor | patch ]
```
Example: ./bin/build.sh gatv 


Note: The program "./bin/build.sh" must to be run from
the main repository folder "$THIS_REPOSITORY_DIR".


##### Learn more about git hooks on the following links:

1. https://git-scm.com/docs/githooks
2. https://git-scm.com/book/en/v2/Customizing-Git-Git-Hooks




