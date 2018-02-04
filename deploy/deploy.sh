#!/bin/bash
set -e  # Exit with non-zero if anything fails

BUILD_BRANCH="master"

# Do not build a new version if it is a pull-request or commit not to BUILD_BRANCH
if [ "$TRAVIS_PULL_REQUEST" != "false" -o "$TRAVIS_BRANCH" != "$BUILD_BRANCH" ]; then
    echo "Not $BUILD_BRANCH, skipping deploy;"
    exit 0
fi

FRONTEND_HEAD_COMMIT=`git rev-parse --verify --short HEAD`
FRONTEND_REPO=`git config remote.origin.url`
BACKEND_NAME="worldwifi-rails"
BACKEND_REPO="git@github.com:oomag/${BACKEND_NAME}.git"
HEROKU_REPO_URL="git@heroku.com:worldwifistaging.git"

echo "Prepare the key..."
# Encryption key is a key stored in travis itself
OUT_KEY="id_rsa"
echo "Trying to decrypt encoded key..."
openssl aes-256-cbc -k "$ENCRYPTION_KEY" -in deploy/id_rsa.enc -out $OUT_KEY -d -md sha256
chmod 600 $OUT_KEY
echo "Add decoded key to the ssh agent keystore"
eval `ssh-agent -s`
ssh-add $OUT_KEY

echo "Pull upstream backend repo"
pushd ..
git clone $BACKEND_REPO $BACKEND_NAME
echo "Return back to the original repo"
popd

echo "Checkout to staging branch in backend repo"
pushd ../$BACKEND_NAME
git checkout staging
popd

echo "Copy data to the backend repo"
mkdir -p ../$BACKEND_NAME/public/
cp -rf ru ../$BACKEND_NAME/public/
cp -rf en ../$BACKEND_NAME/public/

echo "Add new data to the backend repo git"
pushd ../$BACKEND_NAME
git config user.name "Travis CI"
git config user.email "$COMMIT_AUTHOR_EMAIL"

git add -A .
if ! [[ -z $(git status -s) ]] ; then
  echo "Pushing changes to the $BACKEND_REPO staging branch"
  git commit -m "Add new build data from $BACKEND_NAME frontend $HEAD_COMMIT commit"
  git push origin staging
  echo "Add $HEROKU_REPO_URL as heroku remote"
  git remote add heroku $HEROKU_REPO_URL
  echo "Pushing to heroku remote..."
  export GIT_SSH_COMMAND="ssh -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no"
  git push -q --force heroku staging:$BUILD_BRANCH
  echo "All done."
else
  echo "There are no changes in result build, so nothing to push forward. End here."
  exit 0
fi

