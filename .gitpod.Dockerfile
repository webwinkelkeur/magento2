FROM gitpod/workspace-full:2022-05-17-09-53-33

USER root

RUN apt-get update && apt-get install -y mariadb-client

USER root