# Laufkartengenerator

## How to Run

Preparation:
- Change variable my_scurity_secret to your own secret!
- First page: start_count
- Number of pages: page_count

```bash
docker run -it --rm --name laufkartengenerator -v "$PWD":/usr/src/myapp -w /usr/src/myapp python:3 python laufkartengenerator.py
```
