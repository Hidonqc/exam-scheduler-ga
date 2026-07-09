import random
from ga.chromosome import Gene

def mutate(chromosome, scheduler, mutation_rate=0.1):
    
    for i, gene in enumerate(chromosome.genes):
        if random.random() < mutation_rate:
            # 1. Đột biến Ca thi
            new_timeslot = random.choice(scheduler.timeslots)
            
            # 2. Đột biến Tổ hợp phòng thi (Quét lại danh sách phòng ngẫu nhiên)
            new_rooms = []
            num_students = scheduler.sections.get(gene.section_id, 0)
            current_cap = 0
            
            available_rooms = list(scheduler.rooms.keys())
            random.shuffle(available_rooms) 
            
            for r in available_rooms:
                if current_cap >= num_students:
                    break
                new_rooms.append(r)
                current_cap += scheduler.rooms[r]
                
            # Ghi đè Gene mới vào cấu trúc
            chromosome.genes[i] = Gene(gene.section_id, new_timeslot, new_rooms)
