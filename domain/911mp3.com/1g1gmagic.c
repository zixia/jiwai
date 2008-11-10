#include <stdio.h>
#include <stdlib.h>

int computeMagic(int seed)
{
	int l2 = 17;
	int l3 = 604373598;
	int l4 = 0;
	int l5 = seed;
	int l6 = 0;
	int l7 = 0;
	while ( l7 < l2 )
	{
		l6 = (l5 & 1);
//printf("%d - %d\n",l7,l6);
		l5 = ((l5 >> 1) | (l6 << 31));
//printf("%d - %d\n",l7,l5);
		l4 = (l4 + (l3 ^ l5));
//printf("%d - %d\n",l7,l4);
		l7++;
	}
	
	return l4;
}


void main(int argc, char* argv[])
{
	int seed = atoi(argv[1]);//-1502568093;
	printf( "%d", computeMagic(seed) );
}

/*
        public function computeMagic(_arg1:int):int{
            var _local6:int;
            var _local2 = 17;
            var _local3 = 604373598;
            var _local4:int;
            var _local5:int = _arg1;
            var _local7:int;
            while (_local7 < _local2) {
                _local6 = (_local5 & 1);
                _local5 = ((_local5 >> 1) | (_local6 << 31));
                _local4 = (_local4 + (_local3 ^ _local5));
                _local7++;
            };
            return (_local4);
        }
*/
